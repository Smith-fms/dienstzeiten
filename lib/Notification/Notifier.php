<?php

namespace OCA\DienstzeitenApp\Notification;

use OCP\IURLGenerator;
use OCP\IUserManager;
use OCP\L10N\IFactory;
use OCP\Notification\IAction;
use OCP\Notification\INotification;
use OCP\Notification\INotifier;

class Notifier implements INotifier {

    private $factory;
    private $url;
    private $userManager;

    public function __construct(IFactory $factory, IURLGenerator $url, IUserManager $userManager) {
        $this->factory = $factory;
        $this->url = $url;
        $this->userManager = $userManager;
    }

    /**
     * Identifier of the notifier, only use [a-z0-9_]
     */
    public function getID(): string {
        return 'dienstzeiten_app';
    }

    /**
     * Human readable name describing the notifier
     */
    public function getName(): string {
        return $this->factory->get('dienstzeiten_app')->t('Dienstzeiten App');
    }

    /**
     * @param INotification $notification
     * @param string $languageCode The code of the language that should be used to prepare the notification
     * @return INotification
     * @throws \InvalidArgumentException When the notification was not prepared by a notifier
     */
    public function prepare(INotification $notification, string $languageCode): INotification {
        if ($notification->getApp() !== 'dienstzeiten_app') {
            throw new \InvalidArgumentException('Falsche App');
        }

        $l = $this->factory->get('dienstzeiten_app', $languageCode);

        switch ($notification->getSubject()) {
            case 'new_approval_request':
                $params = $notification->getSubjectParameters();
                $dienstzeitId = $params['dienstzeit_id'];
                $requestorName = '';
                
                if (isset($params['user_id'])) {
                    $user = $this->userManager->get($params['user_id']);
                    if ($user !== null) {
                        $requestorName = $user->getDisplayName();
                    }
                }
                
                $dateTime = '';
                if (isset($params['date'])) {
                    try {
                        $date = new \DateTime($params['date']);
                        $dateTime = $date->format('d.m.Y');
                    } catch (\Exception $e) {
                        $dateTime = $params['date'];
                    }
                }
                
                $notification->setRichSubject(
                    $l->t('{user} hat einen neuen Dienstzeit-Eintrag für {date} zur Genehmigung eingereicht'),
                    [
                        'user' => [
                            'type' => 'user',
                            'id' => $params['user_id'] ?? '',
                            'name' => $requestorName,
                        ],
                        'date' => [
                            'type' => 'highlight',
                            'id' => 'date',
                            'name' => $dateTime,
                        ],
                    ]
                );
                
                // Link zur Genehmigungsseite
                $approvalLink = $this->url->linkToRouteAbsolute('dienstzeiten_app.dienstzeiten.show', ['id' => $dienstzeitId]);
                $notification->setLink($approvalLink);
                
                $approveAction = $notification->createAction();
                $approveAction->setLabel($l->t('Öffnen'))
                    ->setPrimary(true)
                    ->setLink($approvalLink, IAction::TYPE_WEB);
                
                $notification->addAction($approveAction);
                
                break;
                
            case 'dienstzeit_approved':
                $params = $notification->getSubjectParameters();
                $dienstzeitId = $params['dienstzeit_id'];
                $approverName = '';
                
                if (isset($params['approver_id'])) {
                    $user = $this->userManager->get($params['approver_id']);
                    if ($user !== null) {
                        $approverName = $user->getDisplayName();
                    }
                }
                
                $dateTime = '';
                if (isset($params['date'])) {
                    try {
                        $date = new \DateTime($params['date']);
                        $dateTime = $date->format('d.m.Y');
                    } catch (\Exception $e) {
                        $dateTime = $params['date'];
                    }
                }
                
                $notification->setRichSubject(
                    $l->t('Ihr Dienstzeit-Eintrag für {date} wurde von {approver} genehmigt'),
                    [
                        'approver' => [
                            'type' => 'user',
                            'id' => $params['approver_id'] ?? '',
                            'name' => $approverName,
                        ],
                        'date' => [
                            'type' => 'highlight',
                            'id' => 'date',
                            'name' => $dateTime,
                        ],
                    ]
                );
                
                // Link zur Detailseite
                $detailLink = $this->url->linkToRouteAbsolute('dienstzeiten_app.dienstzeiten.show', ['id' => $dienstzeitId]);
                $notification->setLink($detailLink);
                
                break;
                
            case 'dienstzeit_rejected':
                $params = $notification->getSubjectParameters();
                $dienstzeitId = $params['dienstzeit_id'];
                $rejecterName = '';
                
                if (isset($params['rejecter_id'])) {
                    $user = $this->userManager->get($params['rejecter_id']);
                    if ($user !== null) {
                        $rejecterName = $user->getDisplayName();
                    }
                }
                
                $dateTime = '';
                if (isset($params['date'])) {
                    try {
                        $date = new \DateTime($params['date']);
                        $dateTime = $date->format('d.m.Y');
                    } catch (\Exception $e) {
                        $dateTime = $params['date'];
                    }
                }
                
                $reason = $params['reason'] ?? $l->t('Kein Grund angegeben');
                
                $notification->setRichSubject(
                    $l->t('Ihr Dienstzeit-Eintrag für {date} wurde von {rejecter} abgelehnt'),
                    [
                        'rejecter' => [
                            'type' => 'user',
                            'id' => $params['rejecter_id'] ?? '',
                            'name' => $rejecterName,
                        ],
                        'date' => [
                            'type' => 'highlight',
                            'id' => 'date',
                            'name' => $dateTime,
                        ],
                    ]
                );
                
                $notification->setMessage($reason);
                
                // Link zur Detailseite
                $detailLink = $this->url->linkToRouteAbsolute('dienstzeiten_app.dienstzeiten.show', ['id' => $dienstzeitId]);
                $notification->setLink($detailLink);
                
                break;
                
            default:
                throw new \InvalidArgumentException('Unbekannter Benachrichtigungstyp');
        }

        return $notification;
    }
}
