<?php

namespace Claroline\AuthenticationBundle\Controller\OAuth2;

use Claroline\AuthenticationBundle\Entity\OAuthClient;
use Claroline\AuthenticationBundle\Manager\OAuthManager;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class AuthenticationController
{
    public function __construct(
        private readonly AuthorizationCheckerInterface $authorization,
        private readonly OAuthManager $oauthManager,
    ) {
    }

    /**
     * Entry point for the OAuth2 authentication process.
     */
    #[Route(path: '/login/oauth2/{clientId}', name: 'apiv2_authentication_oauth2', methods: ['GET'])]
    public function loginAction(
        #[MapEntity(mapping: ['clientId' => 'uuid'])]
        OAuthClient $client,
        Request $request
    ): RedirectResponse {
        if ($this->authorization->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw new AccessDeniedHttpException('You are already logged in.');
        }

        return new RedirectResponse(
            $this->oauthManager->startAuthentication($request, $client)
        );
    }
}
