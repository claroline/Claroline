<?php

namespace Claroline\HomeBundle\Controller;

use Symfony\Bundle\TwigBundle\TwigEngine;

class MainController
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
            'ClarolineHomeBundle:MainController:index.html.twig'
        );
    }
}