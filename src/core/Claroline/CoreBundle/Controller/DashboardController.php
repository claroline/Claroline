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
        $resourceTypes = $this->get('doctrine.orm.entity_manager')
            ->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType')
            ->findBy(array('isListable' => 1));

        return $this->render('ClarolineCoreBundle:Dashboard:resources.html.twig', array('resourceTypes' => $resourceTypes));
    }
}