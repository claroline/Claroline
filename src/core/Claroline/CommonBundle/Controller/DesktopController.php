<?php

namespace Claroline\CommonBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * To be removed ASAP...
 * 
 * @todo Create a dedicated desktop application
 */
class DesktopController extends Controller
{
    public function indexAction()
    {
        $workspaceRepo = $this->getDoctrine()->getRepository('ClarolineWorkspaceBundle:Workspace');
        $workspaces = $workspaceRepo->findAll();
        $assigns  = array(
            'workspaces' => $workspaces,
        );
        return $this->render('ClarolineCommonBundle:Desktop:index.html.twig', $assigns);
    }
}