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
use Claroline\LogBundle\Messenger\Security\Message\UserLogoutMessage;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Logout\LogoutHandlerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class LogoutHandler implements LogoutHandlerInterface
{
    private $messageBus;
    private $translator;

    public function __construct(MessageBusInterface $messageBus, TranslatorInterface $translator)
    {
        $this->messageBus = $messageBus;
        $this->translator = $translator;
    }

    public function logout(Request $request, Response $response, TokenInterface $token)
    {
        if ($token->getUser() instanceof User) {
            // only log if the user isn't already logged out
            // this can occur with SAML : when we call the IDP to request a logout, it will call back to our logout endpoint
            // but our user is no longer in the token storage.
            $this->messageBus->dispatch(new UserLogoutMessage(
                $token->getUser()->getId(),
                $token->getUser()->getId(),
                'event.security.user_logout',
                $this->translator->trans('userLogout', ['username' => $token->getUser()->getUsername()], 'security')
            ));
        }
    }
}
