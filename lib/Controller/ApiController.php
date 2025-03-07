<?php

namespace OCA\DienstzeitenApp\Controller;

use OCP\AppFramework\ApiController as BaseApiController;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCP\IUserManager;
use OCP\IUserSession;

class ApiController extends BaseApiController {

    private $userId;
    private $userManager;
    private $userSession;

    public function __construct(
        string $appName,
        IRequest $request,
        ?string $userId,
        IUserManager $userManager,
        IUserSession $userSession
    ) {
        parent::__construct($appName, $request);
        $this->userId = $userId;
        $this->userManager = $userManager;
        $this->userSession = $userSession;
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function getUserData(): JSONResponse {
        if (!$this->userId) {
            return new JSONResponse(['error' => 'Benutzer nicht angemeldet.'], Http::STATUS_UNAUTHORIZED);
        }
        
        $user = $this->userManager->get($this->userId);
        if (!$user) {
            return new JSONResponse(['error' => 'Benutzer nicht gefunden.'], Http::STATUS_NOT_FOUND);
        }
        
        // Vollständigen Namen in Vor- und Nachname aufteilen (falls möglich)
        $displayName = $user->getDisplayName();
        $nameParts = explode(' ', $displayName, 2);
        $firstName = $nameParts[0] ?? '';
        $lastName = $nameParts[1] ?? '';
        
        return new JSONResponse([
            'user_id' => $this->userId,
            'display_name' => $displayName,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $user->getEMailAddress()
        ]);
    }
}
