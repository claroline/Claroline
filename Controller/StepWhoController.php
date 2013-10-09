<?php

namespace Innova\PathBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

// use Innova\PathBundle\Entity\PathTemplate;

class StepWhoController extends Controller
{
    /**
     * @Route(
     *     "/step/who",
     *     name = "innova_path_get_stepwho",
     *     options = {"expose"=true}
     * )
     *
     * @Method("GET")
     */
    public function getStepWhosAction()
    {
        $results = $this->get('doctrine.orm.entity_manager')->getRepository('InnovaPathBundle:StepWho')->findAll();

        $stepWhos = array();
        foreach ($results as $result) {
            $stepWhos[$result->getId()] = $result->getName();
        }

        return new JsonResponse($stepWhos);
    }
}
