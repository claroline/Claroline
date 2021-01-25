<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 *
 * Date: 12/6/16
 */

namespace Claroline\AuthenticationBundle\Security\Oauth;

use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\AuthenticationBundle\Security\Oauth\Hwi\ResourceOwnerFactory;
use Claroline\CoreBundle\Event\CatalogEvents\SecurityEvents;
use Claroline\CoreBundle\Event\Log\UserLogoutEvent;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\AbstractResourceOwner;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Logout\LogoutHandlerInterface;

class LogoutHandler implements LogoutHandlerInterface
{
    /**
     * @var SessionInterface
     */
    private $session;

    private $resourceOwnerFactory;

    private $eventDispatcher;

    public function __construct(SessionInterface $session, ResourceOwnerFactory $resourceOwnerFactory, StrictDispatcher $eventDispatcher)
    {
        $this->session = $session;
        $this->resourceOwnerFactory = $resourceOwnerFactory;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Logout user from SSO provider if needed.
     */
    public function logout(Request $request, Response $response, TokenInterface $token)
    {
        $resourceOwnerToken = $this->session->get('claroline.oauth.resource_owner_token');
        if (!empty($resourceOwnerToken)) {
            try {
                $resourceOwnerName = str_replace('_', '', ucwords($resourceOwnerToken['resourceOwnerName'], '_'));

                /** @var AbstractResourceOwner $resourceOwner */
                $resourceOwner = $this->resourceOwnerFactory->{'get'.$resourceOwnerName.'ResourceOwner'}();
                if ($resourceOwner) {
                    $resourceOwner->revokeToken($resourceOwnerToken['token']);
                }

                $this->eventDispatcher->dispatch(SecurityEvents::USER_LOGOUT, UserLogoutEvent::class, [$token->getUser()]);
            } catch (AuthenticationException $e) {
                // Do nothing
            }
        }
    }
}
