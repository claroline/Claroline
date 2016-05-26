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
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

/**
 * This is the API Authentication class. It supports Cookies, HTTP, Anonymous & OAUTH authentication.
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
     * @var EntityUserProvider
     */
    protected $userProvider;

     /**
      * @var EncoderFactoryInterface
      */
     protected $encodeFactory;

    /**
     * @DI\InjectParams({
     *     "securityContext"       = @DI\Inject("security.context"),
     *     "authenticationManager" = @DI\Inject("security.authentication.manager"),
     *     "serverService"         = @DI\Inject("fos_oauth_server.server"),
     *     "userProvider"          = @DI\Inject("security.user.provider.concrete.user_db"),
     *     "encodeFactory"         = @DI\Inject("security.encoder_factory")
     *
     * })
     */
    public function __construct(
        SecurityContextInterface $securityContext,
        AuthenticationManagerInterface $authenticationManager,
        OAuth2 $serverService,
        EntityUserProvider $userProvider,
        EncoderFactoryInterface $encodeFactory
    ) {
        $this->securityContext = $securityContext;
        $this->authenticationManager = $authenticationManager;
        $this->serverService = $serverService;
        //always the same, copied from Symfony\Component\Security\Http\Firewall\ContextListener
        $this->sessionKey = '_security_main';
        $this->userProvider = $userProvider;
        $this->encodeFactory = $encodeFactory;
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event The event.
     */
    public function handle(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if (null === $oauthToken = $this->serverService->getBearerToken($event->getRequest(), true)) {
            if ($this->tryCookieAuth($event)) {
                return;
            }
            if ($this->tryHTTPAuth($event)) {
                return;
            }

            $this->authenticateAnonymous();
        } else {
            $this->tryOauthAuth($event, $oauthToken);
        }
    }

    private function tryCookieAuth(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $session = $request->hasPreviousSession() ? $request->getSession() : null;

        if (!$session) {
            return;
        }

        $token = $session->get($this->sessionKey);
        $token = unserialize($token);

        if ($token instanceof TokenInterface) {
            $this->securityContext->setToken($token);
            $this->refreshUser($token);

            return true;
        }

        return false;
    }

    private function tryHTTPAuth(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $username = $request->server->get('PHP_AUTH_USER');
        $password = $request->server->get('PHP_AUTH_PW');

        if (!$username || !$password) {
            return false;
        }

        $user = $this->userProvider->loadUserByUsername($username);
        $providerKey = 'main';

        $token = new UsernamePasswordToken($user, $password, $providerKey, $user->getRoles());

        $encoder = $this->encodeFactory->getEncoder($user);
        $encodedPass = $encoder->encodePassword($password, $user->getSalt());

        //the authenticationManager always never throw an Exception with the UsernamePasswordToken so we validate it manually
        if ($user->getPassword() === $encodedPass) {
            $this->securityContext->setToken($token);

            return true;
        }

        $error = [
                'error' => 'authentication_error',
                'error_description' => 'Invalid username and password combination',
            ];
        $event->setResponse(new Response(json_encode($error)));

        return false;
    }

    private function authenticateAnonymous()
    {
        $token = new AnonymousToken('main', 'anon.', array('ROLE_ANONYMOUS'));
        $this->securityContext->setToken($token);

       /*
        * To do things properly, we should retrieve the anonymous key from the firewall ($firewall['anonymous'][key])
        * It can be set in the security.yml manually or is randomly generated. I don't know how to retrieve the random one yet.
        *
        *    $token = new AnonymousToken($key, 'anon.', array('ROLE_ANONYMOUS'));
        *
        *    try {
        *        $returnValue = $this->authenticationManager->authenticate($token);
        *        if ($returnValue instanceof TokenInterface) {
        *            $this->securityContext->setToken($returnValue);
        *
        *            return true;
        *        }
        *    } catch (AuthenticationException $e) {
        *        return false;
        *    }
        */
    }

    private function tryOauthAuth(GetResponseEvent $event, $oauthToken)
    {
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
