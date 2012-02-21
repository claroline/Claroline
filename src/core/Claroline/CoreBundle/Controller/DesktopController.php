<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Claroline\CoreBundle\Entity\User;

class DesktopController extends Controller
{
    public function indexAction()
    {
        return $this->render(
            'ClarolineCoreBundle:Desktop:desktop.html.twig',
            array('workspaces_block' => $this->renderWorkspaceBlock())
        );
    }
    
    private function renderWorkspaceBlock()
    {
        $workspaceRoles = array();
        $user = $this->get('security.context')->getToken()->getUser();
        
        if ($user instanceof User)
        {
            $workspaceRoles = $user->getWorkspaceRoleCollection(); 
        }
        
        return $this->get('templating')->render(
            'ClarolineCoreBundle:Desktop:workspaces_block.html.twig',
            array('workspace_roles' => $workspaceRoles)
        );
    }
}