<?php

namespace OCA\DienstzeitenApp\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IConfig;
use OCP\IGroupManager;
use OCP\IRequest;

class SettingsController extends Controller {

    private $userId;
    private $config;
    private $groupManager;
    //private $appName;

    public function __construct(
        string $appName,
        IRequest $request,
        ?string $userId,
        IConfig $config,
        IGroupManager $groupManager
    ) {
        parent::__construct($appName, $request);
        $this->appName = $appName;
        $this->userId = $userId;
        $this->config = $config;
        $this->groupManager = $groupManager;
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function index(): TemplateResponse {
        // Nur für Administratoren zugänglich
        if (!$this->groupManager->isAdmin($this->userId)) {
            return new TemplateResponse('core', '403', [], 'guest');
        }
        
        // Einstellungen aus der Datenbank abrufen
        $teamleadGroup = $this->config->getAppValue($this->appName, 'teamlead_group', '');
        $hrEmail = $this->config->getAppValue($this->appName, 'hr_email', '');
        
        // Alle Gruppen für die Dropdown-Liste abrufen
        $groups = $this->groupManager->search('');
        $groupOptions = [];
        foreach ($groups as $group) {
            $groupOptions[] = [
                'id' => $group->getGID(),
                'name' => $group->getDisplayName()
            ];
        }
        
        return new TemplateResponse($this->appName, 'settings', [
            'teamlead_group' => $teamleadGroup,
            'hr_email' => $hrEmail,
            'groups' => $groupOptions
        ]);
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function save(): JSONResponse {
        // Nur für Administratoren zugänglich
        if (!$this->groupManager->isAdmin($this->userId)) {
            return new JSONResponse(['error' => 'Nur Administratoren können Einstellungen ändern.'], Http::STATUS_FORBIDDEN);
        }
        
        $request = $this->request;
        $teamleadGroup = $request->getParam('teamlead_group', '');
        $hrEmail = $request->getParam('hr_email', '');
        
        // Validierung
        if (empty($teamleadGroup)) {
            return new JSONResponse(['error' => 'Bitte wählen Sie eine Gruppe für die Teamleitung aus.'], Http::STATUS_BAD_REQUEST);
        }
        
        if (empty($hrEmail) || !filter_var($hrEmail, FILTER_VALIDATE_EMAIL)) {
            return new JSONResponse(['error' => 'Bitte geben Sie eine gültige E-Mail-Adresse für die Personalabteilung ein.'], Http::STATUS_BAD_REQUEST);
        }
        
        // Überprüfen, ob die angegebene Gruppe existiert
        if (!$this->groupManager->groupExists($teamleadGroup)) {
            return new JSONResponse(['error' => 'Die ausgewählte Gruppe existiert nicht.'], Http::STATUS_BAD_REQUEST);
        }
        
        // Einstellungen speichern
        $this->config->setAppValue($this->appName, 'teamlead_group', $teamleadGroup);
        $this->config->setAppValue($this->appName, 'hr_email', $hrEmail);
        
        return new JSONResponse([
            'success' => true,
            'message' => 'Einstellungen wurden erfolgreich gespeichert.'
        ]);
    }
}
