<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\TwigBundle\TwigEngine;

class AdministrationController
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
            'ClarolineCoreBundle:Administration:index.html.twig'
        );
    }
}