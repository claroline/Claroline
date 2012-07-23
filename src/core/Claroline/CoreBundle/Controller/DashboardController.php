<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Controller of the user's dashboard.
 */
class DashboardController extends Controller
{
    /**
     * Displays the dashboard index.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        return $this->render('ClarolineCoreBundle:Dashboard:index.html.twig');
    }

    /**
     * Displays the resource manager.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function resourceManagerAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $resourcesType = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType')
            ->findAll();
        $registeredWorkspaces = $em->getRepository('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace')
            ->getWorkspacesOfUser($this->get('security.context')->getToken()->getUser());

        return $this->render(
            'ClarolineCoreBundle:Dashboard:resources.html.twig',
            array('resourcesType' => $resourcesType, 'workspaces' => $registeredWorkspaces)
        );
    }
}