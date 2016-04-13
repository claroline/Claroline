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

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
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
            $this->get('translator')->setLocale($request->getLocale());

            return array('service' => $service['name']);
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
        $session = $request->getSession();
        $service = $session->get('icap.oauth.resource_owner');
        $user = $session->get('icap.oauth.user');
        if ($service !== null && $user !== null) {
            $translator = $this->get('translator');
            $translator->setLocale($request->getLocale());
            $form = $this->get('icap.oauth.manager')->getRegistrationForm($user, $translator);
            //$session->remove('icap.oauth.user');

            return array('form' => $form->createView());
        } else {
            return $this->redirectToRoute('claro_security_login');
        }
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
        $session = $request->getSession();
        $service = $session->get('icap.oauth.resource_owner');
        $translator = $this->get('translator');
        $translator->setLocale($request->getLocale());

        return $this->get('icap.oauth.manager')->createNewAccount($request, $translator, $service);
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
            //$session->remove('icap.oauth.user');

            return array();
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
}
