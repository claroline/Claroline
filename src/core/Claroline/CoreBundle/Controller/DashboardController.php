<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller of the user's dashboard.
 */
class DashboardController extends Controller
{
    /**
     * Displays the dashboard index.
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        // There is no real "index" page, it is usually the "information" tab
        // (in the future, this could be set by the administrator)
        return $this->redirect($this->generateUrl('claro_dashboard_info'));
    }

    /**
     * Displays the Info dashboard tab.
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function infoAction()
    {
        return $this->render('ClarolineCoreBundle:Dashboard:info.html.twig');
    }

    /**
     * Displays the Perso dashboard tab.
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function persoAction()
    {
        return $this->render('ClarolineCoreBundle:Dashboard:perso.html.twig');
    }

    /**
     * Displays the resource manager.
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function resourceManagerAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->get('doctrine.orm.entity_manager');
        $roots = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')
            ->getRoots($user);
        $resourceTypes = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType')
            ->findBy(array('isListable' => 1));

        return $this->render(
            'ClarolineCoreBundle:Dashboard:resources.html.twig',
            array(
                'workspaceRoots' => $roots,
                'resourceTypes' => $resourceTypes
            )
        );
    }
}