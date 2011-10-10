<?php

namespace Claroline\CommonBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\SecurityExtraBundle\Annotation\Secure;

/**
 * To be removed ASAP...
 * 
 * @todo Create a dedicated desktop application
 */
class DesktopController extends Controller
{
    /**
     * @Secure(roles="ROLE_USER")
     */
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