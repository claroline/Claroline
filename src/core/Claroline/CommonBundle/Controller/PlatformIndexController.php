<?php

namespace Claroline\CommonBundle\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\TwigBundle\TwigEngine;

class PlatformIndexController
{
    /** Claroline\PluginBundle\Repository\ApplicationRepository */
    private $appRepo;
    /** Symfony\Bundle\FrameworkBundle\Routing\Router */
    private $router;
    /** Symfony\Bundle\TwigBundle\TwigEngine */
    private $twigEngine;
    
    public function __construct(EntityManager $em, Router $router, TwigEngine $twigEngine)
    {
        $this->appRepo = $em->getRepository('Claroline\PluginBundle\Entity\Application');
        $this->router = $router;
        $this->twigEngine = $twigEngine;
    }
    
    public function indexAction()
    {
        $indexApp = $this->appRepo->getIndexApplication();
        
        if ($indexApp !== false)
        {
            $route = $this->router->generate($indexApp->getIndexRoute());
            
            return new RedirectResponse($route);
        }
        
        return $this->twigEngine->renderResponse(
            'ClarolineCommonBundle:PlatformIndex:default_index.html.twig'
        );
    }
}