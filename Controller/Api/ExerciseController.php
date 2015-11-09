<?php

namespace UJM\ExoBundle\Controller\Api;

use Claroline\CoreBundle\Entity\User;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Entity\Hint;
use UJM\ExoBundle\Entity\Paper;
use UJM\ExoBundle\Entity\Question;
use UJM\ExoBundle\Manager\ApiManager;

/**
 * @EXT\Route(
 *     requirements={"id"="\d+"},
 *     options={"expose"=true},
 *     defaults={"_format": "json"}
 * )
 * @EXT\Method("GET")
 */
class ExerciseController
{
    private $manager;

    /**
     * @DI\InjectParams({
     *     "manager" = @DI\Inject("ujm.exo.api_manager")
     * })
     *
     * @param ApiManager $manager
     */
    public function __construct(ApiManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @todo right management
     * @todo full/partial parameter (w/o solution)
     *
     * @EXT\Route("/exercises/{id}")
     *
     * @param Exercise $exercise
     * @return JsonResponse
     */
    public function exportAction(Exercise $exercise)
    {
        return new JsonResponse($this->manager->exportExercise($exercise));
    }

    /**
     * @todo right management
     * @todo max attempt check
     *
     * @EXT\Route("/exercises/{id}/attempts")
     * @EXT\Method("POST")
     * @EXT\ParamConverter("currentUser", options={"authenticatedUser"=true})
     *
     * @param User      $currentUser
     * @param Exercise  $exercise
     * @return JsonResponse
     */
    public function attemptAction(User $currentUser, Exercise $exercise)
    {
        return new JsonResponse($this->manager->openExercise($exercise, $currentUser, false));
    }

    /**
     * @todo right management
     *
     * @EXT\Route("/papers/{paperId}/questions/{questionId}")
     * @EXT\Method("PUT")
     * @EXT\ParamConverter("paper", class="UJMExoBundle:Paper", options={"mapping": {"paperId": "id"}})
     * @EXT\ParamConverter("question", class="UJMExoBundle:Question", options={"mapping": {"questionId": "id"}})
     *
     * @param Request   $request
     * @param Paper     $paper
     * @param Question  $question
     * @return JsonResponse
     */
    public function submitAnswerAction(
        Request $request,
        Paper $paper,
        Question $question
    )
    {
        if ($paper->getEnd()) {
            return new JsonResponse('Paper cannot be edited anymore', 403);
        }

        $data = $request->request->get('data', null);
        $ip = $request->getClientIp();
        $errors = $this->manager->validateAnswerFormat($question, $data);

        if (count($errors) !== 0) {
            return new JsonResponse($errors, 422);
        }

        $this->manager->recordAnswer($paper, $question, $data, $ip);

        return new JsonResponse();
    }

    /**
     * @todo right management
     *
     * @EXT\Route("/papers/{paperId}/hints/{hintId}")
     * @EXT\ParamConverter("paper", class="UJMExoBundle:Paper", options={"mapping": {"paperId": "id"}})
     * @EXT\ParamConverter("hint", class="UJMExoBundle:Hint", options={"mapping": {"hintId": "id"}})
     *
     * @param Paper $paper
     * @param Hint  $hint
     * @return JsonResponse
     */
    public function hintAction(Paper $paper, Hint $hint)
    {
        if (!$this->manager->hasHint($paper, $hint)) {
            return new JsonResponse('Hint and paper are not related', 422);
        }

        return new JsonResponse($this->manager->viewHint($paper, $hint));
    }
}
