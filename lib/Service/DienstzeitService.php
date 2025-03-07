<?php

namespace OCA\DienstzeitenApp\Service;

use OCA\DienstzeitenApp\Db\Dienstzeit;
use OCA\DienstzeitenApp\Db\DienstzeitMapper;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\DB\Exception;
use OCP\Security\ISecureRandom;

class DienstzeitService {

    private $mapper;
    private $secureRandom;

    public function __construct(
        DienstzeitMapper $mapper,
        ISecureRandom $secureRandom
    ) {
        $this->mapper = $mapper;
        $this->secureRandom = $secureRandom;
    }

    /**
     * Erstellt einen neuen Dienstzeit-Eintrag
     *
     * @param string $userId ID des Benutzers
     * @param string $firstName Vorname
     * @param string $lastName Nachname
     * @param string $email E-Mail-Adresse
     * @param string $serviceDate Datum des Dienstes
     * @param string $startTime Startzeit
     * @param string $endTime Endzeit
     * @param string $station Wache
     * @param string $otherDetails Weitere Details (wenn Station = Sonstiges)
     * @param bool $overtimeDueToEmergency Mehrarbeit durch Einsatz
     * @param string $emergencyNumber Einsatznummer (wenn overtimeDueToEmergency = true)
     * @param string $signature Unterschrift (Base64-codierte Daten-URL)
     * @return Dienstzeit Der erstellte Dienstzeit-Eintrag
     * @throws Exception wenn ein Datenbankfehler auftritt
     */
    public function createDienstzeit(
        string $userId,
        string $firstName,
        string $lastName,
        string $email,
        string $serviceDate,
        string $startTime,
        string $endTime,
        string $station,
        string $otherDetails = '',
        bool $overtimeDueToEmergency = false,
        string $emergencyNumber = '',
        string $signature = ''
    ): Dienstzeit {
        // Token für den Genehmigungslink generieren
        $token = $this->secureRandom->generate(32);
        
        $dienstzeit = new Dienstzeit();
        $dienstzeit->setUserId($userId);
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
        
        return $this->mapper->insert($dienstzeit);
    }

    /**
     * Findet einen Dienstzeit-Eintrag anhand seiner ID
     *
     * @param int $id ID des Dienstzeit-Eintrags
     * @return Dienstzeit Der Dienstzeit-Eintrag
     * @throws DoesNotExistException wenn der Eintrag nicht existiert
     * @throws MultipleObjectsReturnedException wenn mehrere Einträge gefunden wurden
     */
    public function findDienstzeit(int $id): Dienstzeit {
        return $this->mapper->find($id);
    }

    /**
     * Findet einen Dienstzeit-Eintrag anhand seiner ID und des Tokens
     *
     * @param int $id ID des Dienstzeit-Eintrags
     * @param string $token Token für den Genehmigungslink
     * @return Dienstzeit Der Dienstzeit-Eintrag
     * @throws DoesNotExistException wenn der Eintrag nicht existiert
     * @throws MultipleObjectsReturnedException wenn mehrere Einträge gefunden wurden
     */
    public function findDienstzeitByIdAndToken(int $id, string $token): Dienstzeit {
        return $this->mapper->findByIdAndToken($id, $token);
    }

    /**
     * Findet alle Dienstzeit-Einträge eines Benutzers
     *
     * @param string $userId ID des Benutzers
     * @return array Array mit Dienstzeit-Einträgen
     */
    public function findAllDienstzeitenForUser(string $userId): array {
        return $this->mapper->findAllForUser($userId);
    }

    /**
     * Aktualisiert den Status eines Dienstzeit-Eintrags
     *
     * @param int $id ID des Dienstzeit-Eintrags
     * @param string $status Neuer Status ('pending', 'approved', 'rejected')
     * @param string $approvedBy ID des genehmigenden Benutzers (wenn status = 'approved')
     * @param string $rejectionReason Grund für die Ablehnung (wenn status = 'rejected')
     * @return Dienstzeit Der aktualisierte Dienstzeit-Eintrag
     * @throws DoesNotExistException wenn der Eintrag nicht existiert
     * @throws Exception wenn ein Datenbankfehler auftritt
     */
    public function updateStatus(int $id, string $status, string $approvedBy = '', string $rejectionReason = ''): Dienstzeit {
        try {
            $dienstzeit = $this->mapper->find($id);
            
            $dienstzeit->setStatus($status);
            
            if ($status === 'approved') {
                $dienstzeit->setApprovedBy($approvedBy);
                $dienstzeit->setApprovedAt(new \DateTime());
            } elseif ($status === 'rejected') {
                $dienstzeit->setRejectionReason($rejectionReason);
            }
            
            return $this->mapper->update($dienstzeit);
        } catch (DoesNotExistException $e) {
            throw $e;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Berechnet die Dienstzeit in Stunden
     *
     * @param Dienstzeit $dienstzeit Der Dienstzeit-Eintrag
     * @return float Die Dienstzeit in Stunden
     */
    public function calculateDienstzeitHours(Dienstzeit $dienstzeit): float {
        $startTime = $dienstzeit->getStartTime();
        $endTime = $dienstzeit->getEndTime();
        
        $interval = $startTime->diff($endTime);
        $hours = $interval->h + ($interval->i / 60);
        
        return round($hours, 2);
    }
}
