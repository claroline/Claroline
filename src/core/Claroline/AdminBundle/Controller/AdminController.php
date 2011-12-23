<?php

namespace Claroline\AdminBundle\Controller;

use Symfony\Bundle\TwigBundle\TwigEngine;

class AdminController
{
    /** Symfony\Bundle\TwigBundle\TwigEngine */
    private $twigEngine;
    
    public function __construct(TwigEngine $twigEngine)
    {
        $this->twigEngine = $twigEngine;
    }
    
    public function indexAction()
    {        
        return $this->twigEngine->renderResponse(
            'ClarolineAdminBundle:AdminController:index.html.twig'
        );
    }
}