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
        return $this->render('ClarolineCoreBundle:Dashboard:resources.html.twig');
    }
}