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
    //todo also check the url and move the xmlHttp part somewhere else.
    public function resourceManagerAction()
    {
        $request = $this->get('request');

        if ($request->isXmlHttpRequest()) {
            $cookie = $request->cookies->all();
            $content = $this->initClassic($cookie);
            $response = new Response($content);
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }

        return $this->render('ClarolineCoreBundle:Dashboard:resources.html.twig');
    }

    private function initClassic($cookie)
    {
        $manager = $this->get('claroline.resource.manager');
        $string = null;
        if (isset($cookie['dynatree_classic-expand'])) {
            $string = $cookie['dynatree_classic-expand'];
        }

        return $manager->initTreeMode($string);
    }
}