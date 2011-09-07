<?php

namespace Claroline\SecurityBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Security\Core\SecurityContext;

class AuthenticationController
{
    private $request;
    private $twigEngigne;
    
    public function __construct(Request $request,
                                TwigEngine $engine)
    {
        $this->request = $request;
        $this->twigEngigne = $engine;
    }
    
    public function loginAction()
    {
        if ($this->request->attributes->has(SecurityContext::AUTHENTICATION_ERROR))
        {
            $error = $this->request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        }
        else
        {
            $error = $this->request->getSession()->get(SecurityContext::AUTHENTICATION_ERROR);
        }

        $lastUsername = $this->request->getSession()->get(SecurityContext::LAST_USERNAME);
        
        return $this->twigEngigne->renderResponse(
            'ClarolineSecurityBundle:Authentication:login.html.twig', 
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