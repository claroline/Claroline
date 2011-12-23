<?php

namespace Claroline\DesktopBundle\Controller;

use Symfony\Bundle\TwigBundle\TwigEngine;

class DesktopController
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
            'ClarolineDesktopBundle:DesktopController:index.html.twig'
        );
    }
}