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

use Claroline\AuthenticationBundle\Security\Oauth\Hwi\ResourceOwnerFactory;
use Claroline\LogBundle\Messenger\Security\Message\UserLogoutMessage;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\AbstractResourceOwner;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Logout\LogoutHandlerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class LogoutHandler implements LogoutHandlerInterface
{
    /**
     * @var SessionInterface
     */
    private $session;

    private $resourceOwnerFactory;

    private $messageBus;
    private $translator;

    public function __construct(
        SessionInterface $session,
        ResourceOwnerFactory $resourceOwnerFactory,
        MessageBusInterface $messageBus,
        TranslatorInterface $translator
    ) {
        $this->session = $session;
        $this->resourceOwnerFactory = $resourceOwnerFactory;
        $this->messageBus = $messageBus;
        $this->translator = $translator;
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
            } catch (AuthenticationException $e) {
                // Do nothing
            }

            $this->messageBus->dispatch(new UserLogoutMessage(
               $token->getUser()->getId(),
               $token->getUser()->getId(),
                $this->translator->trans('userLogout', ['username' => $token->getUser()->getUsername()], 'security')
            ));
        }
    }
}
