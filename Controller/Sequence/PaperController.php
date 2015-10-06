<?php

namespace UJM\ExoBundle\Controller\Sequence;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Entity\Paper;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * PaperController.
 */
class PaperController extends Controller
{

    /**
     * Render the paper list view
     * @Route("/{id}", requirements={"id" = "\d+"}, name="ujm_exercice_papers", options={"expose"=true})
     * @Method("GET")
     */
    public function exercisePapersAction(Exercise $exercise)
    {
        // get user
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        // FAKE Data for development!!


        return $this->render('UJMExoBundle:Sequence:papers.html.twig', array(
                    '_resource' => $exercise
                        )
        );
    }

    /**
     * Get all papers for an exercise and a user
     * Todo : double check that the paper is available for the user since th exo id is given by a JS variable in papers.html.twig
     * @Route("/{id}/papers", requirements={"id" = "\d+"}, name="ujm_get_exercise_papers", options={"expose"=true})
     * @Method("GET")
     */
    public function getExercisePapers(Exercise $exercise)
    {
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $papers = array(
            array(
                'id' => '23',
                'exerciseId' => $exercise->getId(),
                'user' => $user->getUsername(),
                'number' => 1,
                'start' => '22/09/2015 - 09h18m01s',
                'end' => '22/09/2015 - 09h31m46s',
                'interrupted' => false,
                'score' => 12
            ),
            array(
                'id' => '24',
                'exerciseId' => $exercise->getId(),
                'user' => $user->getUsername(),
                'number' => 2,
                'start' => '28/09/2015 - 13h38m52s',
                'end' => '',
                'interrupted' => true,
                'score' => ''
            )
        );
        $response = array();
        $response['status'] = 'success';
        $response['messages'] = array();
        $response['data'] = $papers;
        return new JsonResponse($response);
    }

    /**
     * Get the detail for one paper
     * @Route("/papers/{id}", requirements={"id" = "\d+"}, name="ujm_get_paper", options={"expose"=true})
     * @Method("GET")
     */
    public function getPaper(Paper $paper)
    {
        // get user
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        // FAKE Data for development!!
        $papers = array(
            array(
                'id' => '23',
                'exerciseId' => $paper->getExercise()->getId(),
                'user' => $user->getUsername(),
                'number' => 1,
                'start' => '22/09/2015 - 09h18m01s ',
                'end' => '22/09/2015 - 09h31m46s',
                'interrupted' => false,
                'score' => '0/20'
            ),
            array(
                'id' => '24',
                'exerciseId' => 9,
                'user' => $user->getUsername(),
                'number' => 2,
                'start' => '28/09/2015 - 13h38m52s',
                'end' => '',
                'interrupted' => true,
                'score' => ''
            )
        );



        $response = array();
        $response['status'] = 'success';
        $response['messages'] = array();
        $response['data'] = $papers[0];
        return new JsonResponse($response);
    }

}
