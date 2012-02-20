<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContext;

class AuthenticationController extends Controller
{
    public function loginAction()
    {
        $request = $this->get('request');
        
        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR))
        {
            $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        }
        else
        {
            $error = $request->getSession()->get(SecurityContext::AUTHENTICATION_ERROR);
        }

        $lastUsername = $request->getSession()->get(SecurityContext::LAST_USERNAME);
        
        return $this->render(
            'ClarolineCoreBundle:Authentication:login.html.twig', 
            array(
                'last_username' => $lastUsername,
                'error' => $error
            )
        );
    }

    public function checkAction()
    {
        throw new \RuntimeException(
            'You must configure the check path to be handled by the firewall '
            . 'using form_login in your security firewall configuration.'
        );
    }

    public function logoutAction()
    {
        throw new \RuntimeException(
            'You must activate the logout in your security firewall configuration.'
        );
    }
}