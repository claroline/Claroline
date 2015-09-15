<?php

namespace UJM\ExoBundle\Controller\Sequence;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use UJM\ExoBundle\Entity\Sequence\Step;

/**
 * Description of StepQuestionController.
 */
class StepQuestionController extends Controller
{
    /**
     * get question(s) for a step.
     *
     * @Route("/get/{id}", requirements={"id" = "\d+"}, name="ujm_step_get_questions", options = {"expose" = true})
     * @Method("GET")
     * @ParamConverter("Step", class="UJMExoBundle:Sequence\Step")
     */
    public function getStepQuestions(Step $step)
    {
        $response = array();
        $json = array();

        $response['status'] = 'success';
        $response['messages'] = array();
        $response['data'] = $json;

        return new JsonResponse($response);
    }
}
