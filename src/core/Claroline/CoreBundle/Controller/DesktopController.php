<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Controller of the user's desktop.
 */
class DesktopController extends Controller
{
    /**
     * Displays the desktop index.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $workspace = $user->getPersonnalWorkspace();

        return $this->render('ClarolineCoreBundle:Desktop:index.html.twig', array('workspaceContextId' => $workspace->getId()));
    }
}