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

    /**
     * LogoutHandler constructor.
     *
     * @param SessionInterface     $session
     * @param ResourceOwnerFactory $resourceOwnerFactory
     */
    public function __construct(SessionInterface $session, ResourceOwnerFactory $resourceOwnerFactory)
    {
        $this->session = $session;
        $this->resourceOwnerFactory = $resourceOwnerFactory;
    }


    /**
     * Logout user from SSO provider if needed.
     *
     * @param Request $request
     * @param Response $response
     * @param TokenInterface $token
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
        }
    }
}
