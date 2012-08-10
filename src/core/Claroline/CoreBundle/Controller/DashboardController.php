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
        $request = $this->get('request');

        if ($request->isXmlHttpRequest()) {
            $cookie = $request->cookies->all();
            $manager = $this->get('claroline.resource.manager');
            $string = null;
            if(isset($cookie['dynatree_classic-expand'])){
                $string = $cookie['dynatree_classic-expand'];
            }
            $response = new Response($manager->initClassicMode($string));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }

        return $this->render('ClarolineCoreBundle:Dashboard:resources.html.twig');
    }
}