<?php

namespace OCA\DienstzeitenApp\Service;

use OCP\IGroupManager;
use OCP\IL10N;
use OCP\IUserManager;
use OCP\Mail\IMailer;

class MailService {

    private $mailer;
    private $userManager;
    private $groupManager;
    private $l10n;
    private $appName;

    public function __construct(
        string $appName,
        IMailer $mailer,
        IUserManager $userManager,
        IGroupManager $groupManager,
        IL10N $l10n
    ) {
        $this->appName = $appName;
        $this->mailer = $mailer;
        $this->userManager = $userManager;
        $this->groupManager = $groupManager;
        $this->l10n = $l10n;
    }

    /**
     * Sendet eine E-Mail an eine bestimmte Adresse
     *
     * @param string $to E-Mail-Adresse des Empfängers
     * @param string $subject Betreff der E-Mail
     * @param string $body Text der E-Mail
     * @return bool Erfolg des Sendens
     */
    public function sendMail(string $to, string $subject, string $body): bool {
        try {
            $message = $this->mailer->createMessage();
            $message->setTo([$to => $to]);
            $message->setSubject('[' . $this->appName . '] ' . $subject);
            $message->setPlainBody($body);
            
            $this->mailer->send($message);
            return true;
        } catch (\Exception $e) {
            \OCP\Util::writeLog($this->appName, 'Fehler beim Senden der E-Mail: ' . $e->getMessage(), \OCP\Util::ERROR);
            return false;
        }
    }

    /**
     * Sendet eine E-Mail mit Anhang an eine bestimmte Adresse
     *
     * @param string $to E-Mail-Adresse des Empfängers
     * @param string $subject Betreff der E-Mail
     * @param string $body Text der E-Mail
     * @param string $attachment Inhalt des Anhangs
     * @param string $filename Name des Anhangs
     * @param string $contentType MIME-Typ des Anhangs
     * @return bool Erfolg des Sendens
     */
    public function sendMailWithAttachment(string $to, string $subject, string $body, string $attachment, string $filename, string $contentType): bool {
        try {
            $message = $this->mailer->createMessage();
            $message->setTo([$to => $to]);
            $message->setSubject('[' . $this->appName . '] ' . $subject);
            $message->setPlainBody($body);
            
            $attachmentObject = $this->mailer->createAttachment($attachment, $filename, $contentType);
            $message->attach($attachmentObject);
            
            $this->mailer->send($message);
            return true;
        } catch (\Exception $e) {
            \OCP\Util::writeLog($this->appName, 'Fehler beim Senden der E-Mail mit Anhang: ' . $e->getMessage(), \OCP\Util::ERROR);
            return false;
        }
    }

    /**
     * Sendet eine E-Mail an alle Mitglieder einer Gruppe
     *
     * @param string $groupId ID der Gruppe
     * @param string $subject Betreff der E-Mail
     * @param string $body Text der E-Mail
     * @return bool Erfolg des Sendens
     */
    public function sendMailToGroup(string $groupId, string $subject, string $body): bool {
        try {
            $group = $this->groupManager->get($groupId);
            if (!$group) {
                \OCP\Util::writeLog($this->appName, 'Gruppe nicht gefunden: ' . $groupId, \OCP\Util::ERROR);
                return false;
            }
            
            $recipients = [];
            $users = $group->getUsers();
            
            foreach ($users as $user) {
                $email = $user->getEMailAddress();
                if ($email) {
                    $recipients[$email] = $user->getDisplayName();
                }
            }
            
            if (empty($recipients)) {
                \OCP\Util::writeLog($this->appName, 'Keine E-Mail-Adressen in der Gruppe gefunden: ' . $groupId, \OCP\Util::ERROR);
                return false;
            }
            
            $message = $this->mailer->createMessage();
            $message->setBcc($recipients);
            $message->setSubject('[' . $this->appName . '] ' . $subject);
            $message->setPlainBody($body);
            
            $this->mailer->send($message);
            return true;
        } catch (\Exception $e) {
            \OCP\Util::writeLog($this->appName, 'Fehler beim Senden der Gruppen-E-Mail: ' . $e->getMessage(), \OCP\Util::ERROR);
            return false;
        }
    }
}
