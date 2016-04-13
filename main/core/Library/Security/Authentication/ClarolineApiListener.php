<?php

/*
 * This file is part of the FOSOAuthServerBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Security\Authentication;

use FOS\OAuthServerBundle\Security\Authentication\Token\OAuthToken;
use OAuth2\OAuth2;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Security\User\EntityUserProvider;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * OAuthListener class.
 * This class is pretty much copied from oauthserverbundle. We use it to override what we need (and easy debug).
 *
 * @DI\Service("claroline.core_bundle.library.security.authentication.claroline_api_listener")
 */
class ClarolineApiListener implements ListenerInterface
{
    /**
     * @var \Symfony\Component\Security\Core\SecurityContextInterface
     */
    protected $securityContext;

    /**
     * @var \Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface
     */
    protected $authenticationManager;

    /**
     * @var \OAuth2\OAuth2
     */
    protected $serverService;

    /**
     * @DI\InjectParams({
     *     "securityContext"       = @DI\Inject("security.context"),
     *     "authenticationManager" = @DI\Inject("security.authentication.manager"),
     *     "serverService"         = @DI\Inject("fos_oauth_server.server"),
     *     "userProvider"          = @DI\Inject("security.user.provider.concrete.user_db")
     * })
     */
    public function __construct(
        SecurityContextInterface $securityContext,
        AuthenticationManagerInterface $authenticationManager,
        OAuth2 $serverService,
        EntityUserProvider $userProvider
    ) {
        $this->securityContext = $securityContext;
        $this->authenticationManager = $authenticationManager;
        $this->serverService = $serverService;
        //always the same, copied from Symfony\Component\Security\Http\Firewall\ContextListener
        $this->sessionKey = '_security_main';
        $this->userProvider = $userProvider;
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event The event.
     */
    public function handle(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if (null === $oauthToken = $this->serverService->getBearerToken($event->getRequest(), true)) {
            //if it's null, then we try to regular authentication...
            $token = $this->handleCookie($event);

            if ($token) {
                $this->securityContext->setToken($token);

                return;
            }
        }

        $token = new OAuthToken();
        $token->setToken($oauthToken);
        $returnValue = $this->authenticationManager->authenticate($token);

        try {
            $returnValue = $this->authenticationManager->authenticate($token);

            if ($returnValue instanceof TokenInterface) {
                return $this->securityContext->setToken($returnValue);
            }

            if ($returnValue instanceof Response) {
                return $event->setResponse($returnValue);
            }
        } catch (AuthenticationException $e) {
            if (null !== $p = $e->getPrevious()) {
                $event->setResponse($p->getHttpResponse());
            }
        }
    }

    public function handleCookie(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $session = $request->hasPreviousSession() ? $request->getSession() : null;

        if (!$session) {
            return;
        }

        $token = $session->get($this->sessionKey);
        $token = unserialize($token);

        if ($token instanceof TokenInterface) {
            $token = $this->refreshUser($token);
        } elseif (null !== $token) {
            $token = null;
        }

        return $token;
    }

    /**
     * Refreshes the user by reloading it from the user provider.
     * This method was copied from Symfony\Component\Security\Http\Firewall\ContextListener.
     *
     * @param TokenInterface $token
     *
     * @return TokenInterface|null
     *
     * @throws \RuntimeException
     */
    protected function refreshUser(TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return;
        }

        $refreshedUser = $this->userProvider->refreshUser($user);
        $token->setUser($refreshedUser);

        return $token;
    }
}
