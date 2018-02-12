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

namespace Icap\OAuthBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ConnexionController extends Controller
{
    /**
     * @EXT\Route("/icap_oauth/check_connection", name="icap_oauth_check_connexion")
     * @EXT\Template("IcapOAuthBundle:Connect:check_connexion.html.twig")
     *
     * @param Request $request
     *
     * @return array
     */
    public function checkConnexionAction(Request $request)
    {
        $session = $request->getSession();
        $service = $session->get('icap.oauth.resource_owner');
        $user = $session->get('icap.oauth.user');
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
            $session->remove('icap.oauth.resource_owner');
            $session->remove('icap.oauth.user');

            return $this->redirectToRoute('claro_security_login');
        }
    }

    /**
     * @EXT\Route("/icap_oauth/register", name="icap_oauth_register")
     * @EXT\Template("IcapOAuthBundle:Connect:create_account.html.twig")
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
        $service = $session->get('icap.oauth.resource_owner');
        $user = $session->get('icap.oauth.user');
        if ($service !== null && $user !== null && $selfRegistration === true) {
            $form = $this->get('icap.oauth.manager')->getRegistrationForm($user);

            return ['form' => $form->createView()];
        }

        return $this->redirectToRoute('claro_security_login');
    }

    /**
     * @EXT\Route("/icap_oauth/create_account", name="icap_oauth_create_account")
     * @EXT\Method("POST")
     * @EXT\Template("IcapOAuthBundle:Connect:create_account.html.twig")
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
            $service = $session->get('icap.oauth.resource_owner');
            $translator = $this->get('translator');
            $translator->setLocale($request->getLocale());

            return $this->get('icap.oauth.manager')->createNewAccount($request, $translator, $service);
        }

        return $this->redirectToRoute('icap_oauth_check_connexion');
    }

    /**
     * @EXT\Route("/icap_oauth/login", name="icap_oauth_login")
     * @EXT\Template("IcapOAuthBundle:Connect:link_account.html.twig")
     *
     * @param Request $request
     *
     * @return array
     */
    public function loginAction(Request $request)
    {
        $session = $request->getSession();
        $service = $session->get('icap.oauth.resource_owner');
        $user = $session->get('icap.oauth.user');
        if ($service !== null && $user !== null) {
            $this->get('translator')->setLocale($request->getLocale());

            return [];
        } else {
            return $this->redirectToRoute('claro_security_login');
        }
    }

    /**
     * @EXT\Route("/icap_oauth/link_account", name="icap_oauth_link_account")
     * @EXT\Method("POST")
     * @EXT\Template("IcapOAuthBundle:Connect:link_account.html.twig")
     *
     * @param Request $request
     *
     * @return array
     */
    public function linkAccountAction(Request $request)
    {
        $session = $request->getSession();
        $service = $session->get('icap.oauth.resource_owner');
        $this->get('translator')->setLocale($request->getLocale());

        return $this->get('icap.oauth.manager')->linkAccount($request, $service);
    }

    /**
     * @EXT\Route("/icap_oauth/link_account_mail", name="icap_oauth_link_account_mail")
     * @EXT\Method("GET")
     * @EXT\Template("IcapOAuthBundle:Connect:link_account.html.twig")
     *
     * @param Request $request
     *
     * @return array
     */
    public function linkAccountByMailAction(Request $request)
    {
        $session = $request->getSession();
        $service = $session->get('icap.oauth.resource_owner');
        $username = $session->get('icap.oauth.user')['email'];
        $this->get('translator')->setLocale($request->getLocale());

        return $this->get('icap.oauth.manager')->linkAccount($request, $service, $username);
    }
}
