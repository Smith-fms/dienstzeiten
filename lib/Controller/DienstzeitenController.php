<?php

namespace OCA\DienstzeitenApp\Controller;

use OCA\DienstzeitenApp\Db\Dienstzeit;
use OCA\DienstzeitenApp\Db\DienstzeitMapper;
use OCA\DienstzeitenApp\Service\DienstzeitService;
use OCA\DienstzeitenApp\Service\MailService;
use OCA\DienstzeitenApp\Service\PdfService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\ContentSecurityPolicy;
use OCP\AppFramework\Http\DataDownloadResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\Template\PublicTemplateResponse;
use OCP\IConfig;
use OCP\IRequest;
use OCP\IURLGenerator;
use OCP\IUserManager;
use OCP\Security\ISecureRandom;

class DienstzeitenController extends Controller {

    private $userId;
    private $mapper;
    private $dienstzeitService;
    private $mailService;
    private $pdfService;
    private $urlGenerator;
    private $userManager;
    private $config;
    private $secureRandom;

    public function __construct(
        string $appName,
        IRequest $request,
        ?string $userId,
        DienstzeitMapper $mapper,
        DienstzeitService $dienstzeitService,
        MailService $mailService,
        PdfService $pdfService,
        IURLGenerator $urlGenerator,
        IUserManager $userManager,
        IConfig $config,
        ISecureRandom $secureRandom
    ) {
        parent::__construct($appName, $request);
        $this->userId = $userId;
        $this->mapper = $mapper;
        $this->dienstzeitService = $dienstzeitService;
        $this->mailService = $mailService;
        $this->pdfService = $pdfService;
        $this->urlGenerator = $urlGenerator;
        $this->userManager = $userManager;
        $this->config = $config;
        $this->secureRandom = $secureRandom;
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function index(): TemplateResponse {
        $user = $this->userManager->get($this->userId);
        $userData = [
            'userId' => $this->userId,
            'firstName' => $user->getDisplayName(),
            'lastName' => '',
            'email' => $user->getEMailAddress()
        ];
        
        // Liefert das Formular zum Erfassen eines neuen Dienstzeit-Eintrags
        return new TemplateResponse($this->appName, 'form', [
            'userData' => $userData
        ]);
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function list(): TemplateResponse {
        $dienstzeiten = $this->mapper->findAllForUser($this->userId);
        
        return new TemplateResponse($this->appName, 'list', [
            'dienstzeiten' => $dienstzeiten
        ]);
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function show(int $id): TemplateResponse {
        try {
            $dienstzeit = $this->mapper->find($id);
            
            // Sicherheitsüberprüfung: Nur Einträge des eigenen Benutzers anzeigen
            if ($dienstzeit->getUserId() !== $this->userId) {
                return new TemplateResponse('core', '403', [], 'guest');
            }
            
            return new TemplateResponse($this->appName, 'detail', [
                'dienstzeit' => $dienstzeit
            ]);
        } catch (DoesNotExistException $e) {
            return new TemplateResponse('core', '404', [], 'guest');
        }
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function store(): JSONResponse {
        $request = $this->request;
        
        // Daten aus dem Formular extrahieren
        $firstName = $request->getParam('first_name');
        $lastName = $request->getParam('last_name');
        $email = $request->getParam('email');
        $serviceDate = $request->getParam('service_date');
        $startTime = $request->getParam('start_time');
        $endTime = $request->getParam('end_time');
        $station = $request->getParam('station');
        $otherDetails = $request->getParam('other_details', '');
        $overtimeDueToEmergency = (bool) $request->getParam('overtime_due_to_emergency', false);
        $emergencyNumber = $request->getParam('emergency_number', '');
        $signature = $request->getParam('signature');
        
        // Validierung der Eingabedaten
        if (empty($serviceDate) || empty($startTime) || empty($endTime) || empty($station) || empty($signature)) {
            return new JSONResponse(['error' => 'Bitte füllen Sie alle Pflichtfelder aus.'], Http::STATUS_BAD_REQUEST);
        }
        
        // Wenn "Sonstiges" als Wache ausgewählt ist, muss ein Text eingegeben sein
        if ($station === 'Sonstiges' && empty($otherDetails)) {
            return new JSONResponse(['error' => 'Bitte geben Sie an, um welche sonstige Wache es sich handelt.'], Http::STATUS_BAD_REQUEST);
        }
        
        // Wenn "Mehrarbeit durch Einsatz" ausgewählt ist, muss eine Einsatznummer eingegeben sein
        if ($overtimeDueToEmergency && empty($emergencyNumber)) {
            return new JSONResponse(['error' => 'Bitte geben Sie die Einsatznummer an.'], Http::STATUS_BAD_REQUEST);
        }
        
        try {
            // Sicherheitstoken für den Genehmigungslink generieren
            $token = $this->secureRandom->generate(32);
            
            // Dienstzeit-Eintrag erstellen
            $dienstzeit = new Dienstzeit();
            $dienstzeit->setUserId($this->userId);
            $dienstzeit->setFirstName($firstName);
            $dienstzeit->setLastName($lastName);
            $dienstzeit->setEmail($email);
            $dienstzeit->setServiceDate(new \DateTime($serviceDate));
            $dienstzeit->setStartTime(new \DateTime($startTime));
            $dienstzeit->setEndTime(new \DateTime($endTime));
            $dienstzeit->setStation($station);
            $dienstzeit->setOtherDetails($otherDetails);
            $dienstzeit->setOvertimeDueToEmergency($overtimeDueToEmergency);
            $dienstzeit->setEmergencyNumber($emergencyNumber);
            $dienstzeit->setSignature($signature);
            $dienstzeit->setCreatedAt(new \DateTime());
            $dienstzeit->setStatus('pending');
            $dienstzeit->setToken($token);
            
            // Dienstzeit-Eintrag in der Datenbank speichern
            $dienstzeit = $this->mapper->insert($dienstzeit);
            
            // E-Mail an Teamleitung zur Genehmigung senden
            $this->sendApprovalEmail($dienstzeit);
            
            return new JSONResponse([
                'success' => true,
                'message' => 'Dienstzeit wurde erfolgreich eingereicht und zur Genehmigung weitergeleitet.',
                'id' => $dienstzeit->getId()
            ]);
        } catch (\Exception $e) {
            return new JSONResponse([
                'error' => 'Bei der Verarbeitung der Dienstzeit ist ein Fehler aufgetreten: ' . $e->getMessage()
            ], Http::STATUS_INTERNAL_SERVER_ERROR);
        }
    }
    
    /**
     * Sendet eine E-Mail zur Genehmigung an die Teamleitung
     */
    private function sendApprovalEmail(Dienstzeit $dienstzeit): void {
        // Teamleitung-Gruppe aus den Einstellungen abrufen
        $teamleadGroupId = $this->config->getAppValue($this->appName, 'teamlead_group', '');
        
        if (empty($teamleadGroupId)) {
            // Wenn keine Teamleitung konfiguriert ist, Protokollierung
            \OCP\Util::writeLog($this->appName, 'Keine Teamleitung konfiguriert für Genehmigungen.', \OCP\Util::ERROR);
            return;
        }
        
        // URL für den Genehmigungslink generieren
        $approvalUrl = $this->urlGenerator->linkToRouteAbsolute(
            $this->appName . '.dienstzeiten.approval',
            [
                'id' => $dienstzeit->getId(),
                'token' => $dienstzeit->getToken()
            ]
        );
        
        // E-Mail-Betreff und Inhalt erstellen
        $subject = 'Genehmigung erforderlich: Dienstzeit von ' . $dienstzeit->getFirstName() . ' ' . $dienstzeit->getLastName();
        $body = "Hallo Teamleitung,\n\n"
              . "Ein neuer Dienstzeit-Eintrag von " . $dienstzeit->getFirstName() . " " . $dienstzeit->getLastName() . " erfordert Ihre Genehmigung.\n\n"
              . "Datum: " . $dienstzeit->getServiceDate()->format('d.m.Y') . "\n"
              . "Zeit: " . $dienstzeit->getStartTime()->format('H:i') . " - " . $dienstzeit->getEndTime()->format('H:i') . "\n"
              . "Wache: " . $dienstzeit->getStation() . "\n\n"
              . "Bitte klicken Sie auf den folgenden Link, um den Eintrag zu genehmigen oder abzulehnen:\n"
              . $approvalUrl . "\n\n"
              . "Mit freundlichen Grüßen,\n"
              . "Ihre Dienstzeiten-App";
        
        // E-Mail senden
        $this->mailService->sendMailToGroup($teamleadGroupId, $subject, $body);
    }

    /**
     * @NoCSRFRequired
     * @PublicPage
     */
    public function approval(int $id, string $token): PublicTemplateResponse {
        try {
            $dienstzeit = $this->mapper->findByIdAndToken($id, $token);
            
            // Prüfen, ob der Eintrag bereits bearbeitet wurde
            if ($dienstzeit->getStatus() !== 'pending') {
                $params = [
                    'message' => 'Dieser Dienstzeit-Eintrag wurde bereits bearbeitet.',
                    'status' => $dienstzeit->getStatus()
                ];
                return new PublicTemplateResponse($this->appName, 'approval-result', $params);
            }
            
            $params = [
                'dienstzeit' => $dienstzeit,
                'token' => $token
            ];
            
            $response = new PublicTemplateResponse($this->appName, 'approval', $params);
            $policy = new ContentSecurityPolicy();
            $policy->addAllowedScriptDomain('*');
            $response->setContentSecurityPolicy($policy);
            
            return $response;
        } catch (DoesNotExistException $e) {
            return new PublicTemplateResponse('core', '404', []);
        }
    }

    /**
     * @NoCSRFRequired
     * @PublicPage
     */
    public function processApproval(int $id, string $token): JSONResponse {
        $request = $this->request;
        $action = $request->getParam('action');
        $rejectionReason = $request->getParam('rejection_reason', '');
        
        if (!in_array($action, ['approve', 'reject'])) {
            return new JSONResponse(['error' => 'Ungültige Aktion.'], Http::STATUS_BAD_REQUEST);
        }
        
        try {
            $dienstzeit = $this->mapper->findByIdAndToken($id, $token);
            
            // Prüfen, ob der Eintrag bereits bearbeitet wurde
            if ($dienstzeit->getStatus() !== 'pending') {
                return new JSONResponse([
                    'error' => 'Dieser Dienstzeit-Eintrag wurde bereits bearbeitet.'
                ], Http::STATUS_BAD_REQUEST);
            }
            
            // Eintrag aktualisieren
            if ($action === 'approve') {
                $dienstzeit->setStatus('approved');
                $dienstzeit->setApprovedBy('Administrator'); // Hier könnte die ID des genehmigenden Benutzers gespeichert werden
                $dienstzeit->setApprovedAt(new \DateTime());
                
                // Eintrag speichern
                $dienstzeit = $this->mapper->update($dienstzeit);
                
                // PDF generieren und an Personalabteilung senden
                $this->sendApprovedEmailWithPdf($dienstzeit);
                
                return new JSONResponse([
                    'success' => true,
                    'message' => 'Der Dienstzeit-Eintrag wurde erfolgreich genehmigt.'
                ]);
            } else {
                // Wenn keine Begründung für die Ablehnung angegeben wurde
                if (empty($rejectionReason)) {
                    return new JSONResponse([
                        'error' => 'Bitte geben Sie einen Grund für die Ablehnung an.'
                    ], Http::STATUS_BAD_REQUEST);
                }
                
                $dienstzeit->setStatus('rejected');
                $dienstzeit->setRejectionReason($rejectionReason);
                
                // Eintrag speichern
                $dienstzeit = $this->mapper->update($dienstzeit);
                
                // Benachrichtigung an den Ersteller senden
                $this->sendRejectionEmail($dienstzeit);
                
                return new JSONResponse([
                    'success' => true,
                    'message' => 'Der Dienstzeit-Eintrag wurde abgelehnt.'
                ]);
            }
        } catch (DoesNotExistException $e) {
            return new JSONResponse(['error' => 'Dienstzeit-Eintrag nicht gefunden.'], Http::STATUS_NOT_FOUND);
        } catch (\Exception $e) {
            return new JSONResponse([
                'error' => 'Bei der Verarbeitung ist ein Fehler aufgetreten: ' . $e->getMessage()
            ], Http::STATUS_INTERNAL_SERVER_ERROR);
        }
    }
    
    /**
     * Sendet eine E-Mail mit dem PDF-Anhang an die Personalabteilung
     */
    private function sendApprovedEmailWithPdf(Dienstzeit $dienstzeit): void {
        // E-Mail-Adresse der Personalabteilung aus den Einstellungen abrufen
        $hrEmail = $this->config->getAppValue($this->appName, 'hr_email', '');
        
        if (empty($hrEmail)) {
            // Wenn keine E-Mail-Adresse konfiguriert ist, Protokollierung
            \OCP\Util::writeLog($this->appName, 'Keine E-Mail-Adresse für die Personalabteilung konfiguriert.', \OCP\Util::ERROR);
            return;
        }
        
        // PDF generieren
        $pdf = $this->pdfService->generatePdfForDienstzeit($dienstzeit);
        
        // E-Mail-Betreff und Inhalt erstellen
        $subject = 'Genehmigte Dienstzeit: ' . $dienstzeit->getFirstName() . ' ' . $dienstzeit->getLastName() . ' - ' . $dienstzeit->getServiceDate()->format('d.m.Y');
        $body = "Hallo Personalabteilung,\n\n"
              . "Anbei finden Sie einen genehmigten Dienstzeit-Eintrag:\n\n"
              . "Mitarbeiter: " . $dienstzeit->getFirstName() . " " . $dienstzeit->getLastName() . "\n"
              . "Datum: " . $dienstzeit->getServiceDate()->format('d.m.Y') . "\n"
              . "Zeit: " . $dienstzeit->getStartTime()->format('H:i') . " - " . $dienstzeit->getEndTime()->format('H:i') . "\n"
              . "Wache: " . $dienstzeit->getStation() . "\n\n"
              . "Mit freundlichen Grüßen,\n"
              . "Ihre Dienstzeiten-App";
        
        // E-Mail mit PDF-Anhang senden
        $filename = 'Dienstzeit_' . $dienstzeit->getId() . '_' . $dienstzeit->getServiceDate()->format('Y-m-d') . '.pdf';
        $this->mailService->sendMailWithAttachment($hrEmail, $subject, $body, $pdf, $filename, 'application/pdf');
        
        // Bestätigungs-E-Mail an den Mitarbeiter senden
        $this->sendApprovalConfirmationEmail($dienstzeit);
    }
    
    /**
     * Sendet eine Bestätigungs-E-Mail an den Mitarbeiter
     */
    private function sendApprovalConfirmationEmail(Dienstzeit $dienstzeit): void {
        $subject = 'Ihre Dienstzeit wurde genehmigt';
        $body = "Hallo " . $dienstzeit->getFirstName() . " " . $dienstzeit->getLastName() . ",\n\n"
              . "Ihre Dienstzeit für " . $dienstzeit->getServiceDate()->format('d.m.Y') . " wurde genehmigt.\n\n"
              . "Details:\n"
              . "Datum: " . $dienstzeit->getServiceDate()->format('d.m.Y') . "\n"
              . "Zeit: " . $dienstzeit->getStartTime()->format('H:i') . " - " . $dienstzeit->getEndTime()->format('H:i') . "\n"
              . "Wache: " . $dienstzeit->getStation() . "\n\n"
              . "Mit freundlichen Grüßen,\n"
              . "Ihre Dienstzeiten-App";
        
        $this->mailService->sendMail($dienstzeit->getEmail(), $subject, $body);
    }
    
    /**
     * Sendet eine Ablehnungs-E-Mail an den Mitarbeiter
     */
    private function sendRejectionEmail(Dienstzeit $dienstzeit): void {
        $subject = 'Ihre Dienstzeit wurde abgelehnt';
        $body = "Hallo " . $dienstzeit->getFirstName() . " " . $dienstzeit->getLastName() . ",\n\n"
              . "Ihre Dienstzeit für " . $dienstzeit->getServiceDate()->format('d.m.Y') . " wurde abgelehnt.\n\n"
              . "Begründung: " . $dienstzeit->getRejectionReason() . "\n\n"
              . "Details:\n"
              . "Datum: " . $dienstzeit->getServiceDate()->format('d.m.Y') . "\n"
              . "Zeit: " . $dienstzeit->getStartTime()->format('H:i') . " - " . $dienstzeit->getEndTime()->format('H:i') . "\n"
              . "Wache: " . $dienstzeit->getStation() . "\n\n"
              . "Mit freundlichen Grüßen,\n"
              . "Ihre Dienstzeiten-App";
        
        $this->mailService->sendMail($dienstzeit->getEmail(), $subject, $body);
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function downloadPdf(int $id): DataDownloadResponse {
        try {
            $dienstzeit = $this->mapper->find($id);
            
            // Sicherheitsüberprüfung: Nur Einträge des eigenen Benutzers herunterladen
            if ($dienstzeit->getUserId() !== $this->userId) {
                return new DataDownloadResponse('', 403, 'text/plain');
            }
            
            // PDF generieren
            $pdf = $this->pdfService->generatePdfForDienstzeit($dienstzeit);
            
            // Dateiname generieren
            $filename = 'Dienstzeit_' . $dienstzeit->getId() . '_' . $dienstzeit->getServiceDate()->format('Y-m-d') . '.pdf';
            
            // PDF als Download senden
            return new DataDownloadResponse($pdf, $filename, 'application/pdf');
        } catch (DoesNotExistException $e) {
            return new DataDownloadResponse('', 404, 'text/plain');
        } catch (\Exception $e) {
            return new DataDownloadResponse('', 500, 'text/plain');
        }
    }
            