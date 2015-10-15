<?php

/*
 * This file is part of the FOSOAuthServerBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller;

use FOS\OAuthServerBundle\Event\OAuthEvent;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\Request;
use FOS\OAuthServerBundle\Controller\AuthorizeController as BaseAuthorizeController;
use FOS\OAuthServerBundle\Form\Handler\AuthorizeFormHandler;
use Symfony\Component\Security\Core\SecurityContext;
use Claroline\CoreBundle\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Claroline\CoreBundle\Entity\Oauth\FriendRequest;

/**
 * Controller handling basic authorization
 */
class OauthController extends BaseAuthorizeController
{
    /**
     * @EXT\Route("/oauth/v2/auth_login", name="claro_api_oauth_login")
     * @EXT\Template("ClarolineCoreBundle:Authentication:oauthLogin.html.twig")
     */
    public function oauthLoginAction(Request $request)
    {
        $lastUsername = $request->getSession()->get(SecurityContext::LAST_USERNAME);
        $user         = $this->container->get('claroline.manager.user_manager')->getUserByUsername($lastUsername);

        if ($user && !$user->isAccountNonExpired()) {
            return array(
                'last_username' => $lastUsername,
                'error'         => false,
                'is_expired'    => true
            );
        }

        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        } else {
            $error = $request->getSession()->get(SecurityContext::AUTHENTICATION_ERROR);
        }

        return array(
            'last_username' => $lastUsername,
            'error'         => $error,
            'is_expired'    => false
        );
    }

    /**
     * @EXT\Route("/oauth/v2/auth_login_check", name="claro_api_oauth_login_check")
     * @EXT\Template
     */
    public function loginCheckAction(Request $request)
    {
        // The security layer will intercept this request
    }

    /**
     * @EXT\Route(
     *     "/oauth/v2/auth",
     *     name="claro_oauth_authorize"
     * )
     */
    public function authorizeAction(Request $request)
    {
        $user = $this->container->get('security.context')->getToken()->getUser();

        if (!$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        if (true === $this->container->get('session')->get('_fos_oauth_server.ensure_logout')) {
            $this->container->get('session')->invalidate(600);
            $this->container->get('session')->set('_fos_oauth_server.ensure_logout', true);
        }

        $form = $this->container->get('fos_oauth_server.authorize.form');
        $formHandler = $this->container->get('fos_oauth_server.authorize.form.handler');

        $event = $this->container->get('event_dispatcher')->dispatch(
            OAuthEvent::PRE_AUTHORIZATION_PROCESS,
            new OAuthEvent($user, $this->getClient())
        );

        if ($event->isAuthorizedClient()) {
            $scope = $this->container->get('request')->get('scope', null);

            return $this->container
                ->get('fos_oauth_server.server')
                ->finishClientAuthorization(true, $user, $request, $scope);
        }

        if (true === $formHandler->process()) {
            return $this->processSuccess($user, $formHandler, $request);
        }

        return $this->container->get('templating')->renderResponse(
            'ClarolineCoreBundle:Authentication:oauth_authorize.html.twig',
            array(
                'form'      => $form->createView(),
                'client'    => $this->getClient(),
            )
        );
    }

    /**
     * @param UserInterface        $user
     * @param AuthorizeFormHandler $formHandler
     *
     * @return Response
     */
    protected function processSuccess(UserInterface $user, AuthorizeFormHandler $formHandler, Request $request)
    {
        if (true === $this->container->get('session')->get('_fos_oauth_server.ensure_logout')) {
            $this->container->get('security.context')->setToken(null);
            $this->container->get('session')->invalidate();
        }

        $this->container->get('event_dispatcher')->dispatch(
            OAuthEvent::POST_AUTHORIZATION_PROCESS,
            new OAuthEvent($user, $this->getClient(), $formHandler->isAccepted())
        );

        $formName = $this->container->get('fos_oauth_server.authorize.form')->getName();
        if (!$request->query->all() && $request->request->has($formName)) {
            $request->query->add($request->request->get($formName));
        }

        try {
            //always work

            return $this->container
                ->get('fos_oauth_server.server')
                ->finishClientAuthorization(true, $user, $request, $formHandler->getScope());

            return $this->container
                ->get('fos_oauth_server.server')
                ->finishClientAuthorization($formHandler->isAccepted(), $user, $request, $formHandler->getScope());
        } catch (OAuth2ServerException $e) {
            return $e->getHttpResponse();
        }
    }

    /**
     * @EXT\Route(
     *     "/oauth/v2/log/{name}",
     *     name="claro_oauth_log"
     * )
     */
    public function logAction($name)
    {
        $authCode = $this->container->get('request')->query->get('code');
        $curlManager = $this->container->get('claroline.manager.curl_manager');
        $friendRequest = $this->container->get('claroline.manager.oauth_manager')->findFriendRequestByName($name);
        $access = $friendRequest->getClarolineAccess();
        $redirect = $this->container->get('request')->getSchemeAndHttpHost() .
            $this->container->get('router')->getContext()->getBaseUrl() . '/oauth/v2/log/' . $name;

        //request the token
        $url = $friendRequest->getHost() . '/oauth/v2/token?client_id=' . urlencode($access->getRandomId()) . '&client_secret='
            . urlencode($access->getSecret()) . '&grant_type=authorization_code&redirect_uri=' . urlencode($redirect)
            . '&code=' . urlencode($authCode);

        $data = json_decode($curlManager->exec($url), true);
        var_dump($data);
        $accessToken = $data['access_token'];
        //maybe store the user token one way or an other ?

        $url = $friendRequest->getHost() . '/api/connected_user?access_token=' . $accessToken;
        $data = json_decode($curlManager->exec($url), true);
        $email = $data['email'];
        $userRepo = $this->container->get('doctrine.orm.entity_manager')->getRepository('ClarolineCoreBundle:User');
        $users = $userRepo->findByMail($email);

        if (count($users) === 0) {
            $username = $data['username'];
            $users = $userRepo->findByUsername($username);

            if (count($users) > 0) {
                $username .= uniqd();
            }

            $user = new User();
            $user->setUsername($username);
            $user->setMail($email);
            $user->setFirstName($data['first_name']);
            $user->setLastName($data['last_name']);
            $pw = uniqid();
            $user->setPlainPassword($pw);
            $user = $this->container->get('claroline.manager.user_manager')->createUser($user, false);
        } else {
            $user = $users[0];
        }

        $this->container->get('claroline.authenticator')->authenticate($user->getUsername(), null, false);

        return new RedirectResponse($this->container->get('router')->generate('claro_desktop_open'));
    }
}
