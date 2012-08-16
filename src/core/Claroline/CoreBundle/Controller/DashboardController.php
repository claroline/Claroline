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
        return $this->render('ClarolineCoreBundle:Dashboard:resources.html.twig');
    }
}