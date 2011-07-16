<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DesktopController extends Controller
{
    public function indexAction()
    {
        $workspaceRepo = $this->getDoctrine()->getRepository('ClarolineWorkspaceBundle:Workspace');
        $workspaces = $workspaceRepo->findAll();
        $assigns  = array(
            'workspaces' => $workspaces,
        );
        return $this->render('ClarolineCoreBundle:Desktop:index.html.twig', $assigns);
    }
}