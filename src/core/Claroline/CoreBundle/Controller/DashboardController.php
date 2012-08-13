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
    //todo also check the url and move the xmlHttp part somewhere else.
    public function resourceManagerAction()
    {
        $request = $this->get('request');

        if ($request->isXmlHttpRequest()) {
            $cookie = $request->cookies->all();

            if (isset($cookie['displayMode'])) {

                switch($cookie['displayMode']) {
                    case 'classic': $content = $this->initClassic($cookie);break;
                    case 'hybrid' : $content = $this->initHybrid($cookie);break;
                    case 'linker' : $content = $this->initLinker($cookie);break;
                }
            } else {
                $content = $this->initClassic($cookie);
            }

            $response = new Response($content);
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }

        return $this->render('ClarolineCoreBundle:Dashboard:resources.html.twig');
    }

    private function initClassic($cookie)
    {
        $manager = $this->get('claroline.resource.manager');
        $string = '';
        if (isset($cookie['dynatree_classic-expand'])) {
            $string = $cookie['dynatree_classic-expand'];
        } else

        return $manager->initTreeMode($string);
    }

    private function initHybrid($cookie)
    {
        $manager = $this->get('claroline.resource.manager');
        $string = '';
        if (isset($cookie['dynatree_hybrid-expand'])) {
            $string = $cookie['dynatree_hybrid-expand'];
        }
        //directory type id is 2
        return $manager->initTreeMode($string, 2);
    }

   private function initLinker($cookie)
    {
        $manager = $this->get('claroline.resource.manager');
        $string = '';
        if (isset($cookie['dynatree_linker-expand'])) {
            $string = $cookie['dynatree_linker-expand'];
        }

        return $manager->initLinkerMode($string);
    }

}