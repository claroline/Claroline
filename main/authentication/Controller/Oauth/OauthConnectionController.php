<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 *
 * Date: 7/6/15
 */

namespace Claroline\AuthenticationBundle\Controller\Oauth;

use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class OauthConnectionController extends Controller
{
    /**
     * @EXT\Route("/check_connection", name="claro_oauth_check_connexion")
     * @EXT\Template("ClarolineAuthenticationBundle:oauth:connect\check_connexion.html.twig")
     *
     * @param Request $request
     *
     * @return array
     */
    public function checkConnexionAction(Request $request)
    {
        $session = $request->getSession();
        $service = $session->get('claroline.oauth.resource_owner');
        $user = $session->get('claroline.oauth.user');
        if ($service !== null && $user !== null) {
            $selfRegistration = $this
                ->get('claroline.config.platform_config_handler')
                ->getParameter('allow_self_registration');
            $this->get('translator')->setLocale($request->getLocale());

            return [
                'service' => $service['name'],
                'oauthUser' => $user,
                'selfRegistration' => $selfRegistration,
            ];
        } else {
            $session->remove('claroline.oauth.resource_owner');
            $session->remove('claroline.oauth.user');

            return $this->redirectToRoute('claro_security_login');
        }
    }

    /**
     * @EXT\Route("/register", name="claro_oauth_register")
     * @EXT\Template("ClarolineAuthenticationBundle:oauth:connect\create_account.html.twig")
     *
     * @param Request $request
     *
     * @return array
     */
    public function registerAction(Request $request)
    {
        $selfRegistration = $this
            ->get('claroline.config.platform_config_handler')
            ->getParameter('allow_self_registration');
        $session = $request->getSession();
        $service = $session->get('claroline.oauth.resource_owner');
        $user = $session->get('claroline.oauth.user');
        if ($service !== null && $user !== null && $selfRegistration === true) {
            $form = $this->get('claroline.oauth.manager')->getRegistrationForm($user);

            return ['form' => $form->createView()];
        }

        return $this->redirectToRoute('claro_security_login');
    }

    /**
     * @EXT\Route("/create_account", name="claro_oauth_create_account")
     * @EXT\Method("POST")
     * @EXT\Template("ClarolineAuthenticationBundle:oauth:connect\create_account.html.twig")
     *
     * @param Request $request
     *
     * @return array
     */
    public function createAccountAction(Request $request)
    {
        $selfRegistration = $this
            ->get('claroline.config.platform_config_handler')
            ->getParameter('allow_self_registration');
        if ($selfRegistration) {
            $session = $request->getSession();
            $service = $session->get('claroline.oauth.resource_owner');
            $translator = $this->get('translator');
            $translator->setLocale($request->getLocale());

            return $this->get('claroline.oauth.manager')->createNewAccount($request, $translator, $service);
        }

        return $this->redirectToRoute('claco_oauth_check_connexion');
    }

    /**
     * @EXT\Route("/login", name="claro_oauth_login")
     * @EXT\Template("ClarolineAuthenticationBundle:oauth:connect\link_account.html.twig")
     *
     * @param Request $request
     *
     * @return array
     */
    public function loginAction(Request $request)
    {
        $session = $request->getSession();
        $service = $session->get('claroline.oauth.resource_owner');
        $user = $session->get('claroline.oauth.user');
        if ($service !== null && $user !== null) {
            $this->get('translator')->setLocale($request->getLocale());

            return [];
        } else {
            return $this->redirectToRoute('claro_security_login');
        }
    }

    /**
     * @EXT\Route("/link_account", name="claro_oauth_link_account")
     * @EXT\Method("POST")
     * @EXT\Template("ClarolineAuthenticationBundle:oauth:connect\link_account.html.twig")
     *
     * @param Request $request
     *
     * @return array
     */
    public function linkAccountAction(Request $request)
    {
        $session = $request->getSession();
        $service = $session->get('claroline.oauth.resource_owner');
        $this->get('translator')->setLocale($request->getLocale());

        return $this->get('claroline.oauth.manager')->linkAccount($request, $service);
    }

    /**
     * @EXT\Route("/link_account_mail", name="claro_oauth_link_account_mail")
     * @EXT\Method("GET")
     * @EXT\Template("ClarolineAuthenticationBundle:oauth:connect\link_account.html.twig")
     *
     * @param Request $request
     *
     * @return array
     */
    public function linkAccountByMailAction(Request $request)
    {
        $session = $request->getSession();
        $service = $session->get('claroline.oauth.resource_owner');
        $username = $session->get('claroline.oauth.user')['email'];
        $this->get('translator')->setLocale($request->getLocale());

        return $this->get('claroline.oauth.manager')->linkAccount($request, $service, $username);
    }
}
