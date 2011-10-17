<?php

namespace Claroline\DesktopBundle\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\TwigBundle\TwigEngine;
use JMS\SecurityExtraBundle\Annotation\Secure;

class MainController
{
    /** Doctrine\ORM\EntityManager */
    private $em;
    /** Symfony\Bundle\TwigBundle\TwigEngine */
    private $twigEngine;
    
    public function __construct(EntityManager $em, TwigEngine $twigEngine)
    {
        $this->em = $em;
        $this->twigEngine = $twigEngine;
    }
    
    /**
     * @Secure(roles="ROLE_USER")
     */
    public function indexAction()
    {
        $workspaceRepo = $this->em->getRepository('ClarolineWorkspaceBundle:Workspace');
        $workspaces = $workspaceRepo->findAll();
        $assigns  = array(
            'workspaces' => $workspaces,
        );
        
        return $this->twigEngine->renderResponse(
            'ClarolineDesktopBundle:MainController:index.html.twig',
            $assigns
        );
    }
}