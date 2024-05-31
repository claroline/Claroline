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

namespace Claroline\AuthenticationBundle\Security\Logout;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\CatalogEvents\SecurityEvents;
use Claroline\CoreBundle\Event\Security\UserLogoutEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Logout\LogoutHandlerInterface;

class LogoutHandler implements LogoutHandlerInterface
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function logout(Request $request, Response $response, TokenInterface $token): void
    {
        if ($token->getUser() instanceof User) {
            // only log if the user isn't already logged out
            // this can occur with SAML : when we call the IDP to request a logout, it will call back to our logout endpoint
            // but our user is no longer in the token storage.
            $this->eventDispatcher->dispatch(new UserLogoutEvent($token->getUser()), SecurityEvents::USER_LOGOUT);
        }
    }
}
