<?php

namespace Innova\PathBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class StepWhereController extends Controller
{
    /**
     * @Route(
     *     "/step/where",
     *     name = "innova_path_get_stepwhere",
     *     options = {"expose"=true}
     * )
     *
     * @Method("GET")
     */
    public function getStepWheresAction()
    {
        $results = $this->get('doctrine.orm.entity_manager')->getRepository('InnovaPathBundle:StepWhere')->findAll();

        $stepWheres = array();
        foreach ($results as $result) {
            $stepWheres[$result->getId()] = $result->getName();
        }

        return new JsonResponse($stepWheres);
    }
}
