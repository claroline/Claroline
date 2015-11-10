<?php

namespace UJM\ExoBundle\Controller\Api;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Resource\ResourceCollection;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Entity\Hint;
use UJM\ExoBundle\Entity\Paper;
use UJM\ExoBundle\Entity\Question;
use UJM\ExoBundle\Manager\ApiManager;

/**
 * @EXT\Route(requirements={"id"="\d+"}, options={"expose"=true}, defaults={"_format": "json"})
 * @EXT\Method("GET")
 */
class ExerciseController
{
    private $authorization;
    private $manager;

    /**
     * @DI\InjectParams({
     *     "authorization"  = @DI\Inject("security.authorization_checker"),
     *     "manager"        = @DI\Inject("ujm.exo.api_manager")
     * })
     *
     * @param AuthorizationCheckerInterface $authorization
     * @param ApiManager                    $manager
     */
    public function __construct(AuthorizationCheckerInterface $authorization, ApiManager $manager)
    {
        $this->authorization = $authorization;
        $this->manager = $manager;
    }

    /**
     * Exports the full representation of an exercise (including solutions)
     * in a JSON format.
     *
     * @EXT\Route("/exercises/{id}")
     *
     * @param Exercise $exercise
     * @return JsonResponse
     */
    public function exportAction(Exercise $exercise)
    {
        $this->assertHasPermission('ADMINISTRATE', $exercise);

        return new JsonResponse($this->manager->exportExercise($exercise));
    }

    /**
     * @todo max attempt check
     *
     * Opens an exercise, creating a new paper or re-using an unfinished one.
     *
     * @EXT\Route("/exercises/{id}/attempts")
     * @EXT\Method("POST")
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * @param User      $user
     * @param Exercise  $exercise
     * @return JsonResponse
     */
    public function attemptAction(User $user, Exercise $exercise)
    {
        $this->assertHasPermission('OPEN', $exercise);

        return new JsonResponse($this->manager->openExercise($exercise, $user, false));
    }

    /**
     * Records an answer to an exercise question.
     *
     * @EXT\Route("/papers/{paperId}/questions/{questionId}")
     * @EXT\Method("PUT")
     * @EXT\ParamConverter("user", converter="current_user")
     * @EXT\ParamConverter("paper", class="UJMExoBundle:Paper", options={"mapping": {"paperId": "id"}})
     * @EXT\ParamConverter("question", class="UJMExoBundle:Question", options={"mapping": {"questionId": "id"}})
     *
     * @param User      $user
     * @param Request   $request
     * @param Paper     $paper
     * @param Question  $question
     * @return JsonResponse
     */
    public function submitAnswerAction(User $user, Request $request, Paper $paper, Question $question)
    {
        $this->assertHasPaperAccess($user, $paper);

        $data = $request->request->all();
        $errors = $this->manager->validateAnswerFormat($question, $data);

        if (count($errors) !== 0) {
            return new JsonResponse($errors, 422);
        }

        $this->manager->recordAnswer($paper, $question, $data, $request->getClientIp());

        return new JsonResponse('', 204);
    }

    /**
     * Returns the value of a question hint, and records the fact that it has
     * been consulted within the context of a given paper.
     *
     * @EXT\Route("/papers/{paperId}/hints/{hintId}")
     * @EXT\ParamConverter("user", converter="current_user")
     * @EXT\ParamConverter("paper", class="UJMExoBundle:Paper", options={"mapping": {"paperId": "id"}})
     * @EXT\ParamConverter("hint", class="UJMExoBundle:Hint", options={"mapping": {"hintId": "id"}})
     *
     * @param User  $user
     * @param Paper $paper
     * @param Hint  $hint
     * @return JsonResponse
     */
    public function hintAction(User $user, Paper $paper, Hint $hint)
    {
        $this->assertHasPaperAccess($user, $paper);

        if (!$this->manager->hasHint($paper, $hint)) {
            return new JsonResponse('Hint and paper are not related', 422);
        }

        return new JsonResponse($this->manager->viewHint($paper, $hint));
    }

    /**
     * Marks a paper as finished.
     *
     * @EXT\Route("/papers/{id}/end")
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * @param User  $user
     * @param Paper $paper
     * @return JsonResponse
     */
    public function finishPaperAction(User $user, Paper $paper)
    {
        if ($user !== $paper->getUser()) {
            throw new AccessDeniedHttpException();
        }

        $this->manager->finishPaper($paper);

        return new JsonResponse('', 204);
    }

    /**
     * Returns all the papers associated with an exercise for a given user.
     *
     * @EXT\Route("/exercises/{id}/papers")
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * @param User      $currentUser
     * @param Exercise  $exercise
     * @return JsonResponse
     */
    public function papersAction(User $currentUser, Exercise $exercise)
    {
        return new JsonResponse($this->manager->exportUserPapers($exercise, $currentUser));
    }

    private function assertHasPermission($permission, Exercise $exercise)
    {
        if (!$this->authorization->isGranted($permission, new ResourceCollection([$exercise]))) {
            throw new AccessDeniedHttpException();
        }
    }

    private function assertHasPaperAccess(User $user, Paper $paper)
    {
        if ($paper->getEnd() || $user !== $paper->getUser()) {
            throw new AccessDeniedHttpException();
        }
    }
}
