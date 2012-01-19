<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Claroline\CoreBundle\Entity\User;

class DesktopController
{
    private $twigEngine;
    private $securityContext;
    
    public function __construct(
        SecurityContext $securityContext, 
        TwigEngine $twigEngine
    )
    {
        $this->securityContext = $securityContext;
        $this->twigEngine = $twigEngine;
    }
    
    public function indexAction()
    {
        return $this->twigEngine->renderResponse(
            'ClarolineCoreBundle:Desktop:desktop.html.twig',
            array('workspaces_block' => $this->renderWorkspaceBlock())
        );
    }
    
    private function renderWorkspaceBlock()
    {
        $workspaces = array();
        $user = $this->securityContext->getToken()->getUser();
        
        if ($user instanceof User)
        {
            $workspaces = $user->getWorkspaces(); 
        }
        
        return $this->twigEngine->render(
            'ClarolineCoreBundle:Desktop:workspaces_block.html.twig',
            array('workspaces' => $workspaces)
        );
    }
}