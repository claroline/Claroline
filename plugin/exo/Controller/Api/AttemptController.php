<?php

namespace UJM\ExoBundle\Controller\Api;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Security\Collection\ResourceCollection;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use UJM\ExoBundle\Entity\Attempt\Paper;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Library\Validator\ValidationException;
use UJM\ExoBundle\Manager\Attempt\PaperManager;
use UJM\ExoBundle\Manager\AttemptManager;

/**
 * Attempt Controller.
 *
 * @EXT\Route("/exercises/{exerciseId}/attempts", options={"expose"=true})
 * @EXT\ParamConverter("exercise", class="UJMExoBundle:Exercise", options={"mapping": {"exerciseId": "uuid"}})
 */
class AttemptController extends AbstractController
{
    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorization;

    /**
     * @var AttemptManager
     */
    private $attemptManager;

    /**
     * @var PaperManager
     */
    private $paperManager;

    /**
     * AttemptController constructor.
     *
     * @DI\InjectParams({
     *     "authorization" = @DI\Inject("security.authorization_checker"),
     *     "attemptManager" = @DI\Inject("ujm_exo.manager.attempt"),
     *     "paperManager" = @DI\Inject("ujm_exo.manager.paper")
     * })
     *
     * @param AuthorizationCheckerInterface $authorization
     * @param AttemptManager                $attemptManager
     * @param PaperManager                  $paperManager
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        AttemptManager $attemptManager,
        PaperManager $paperManager)
    {
        $this->authorization = $authorization;
        $this->attemptManager = $attemptManager;
        $this->paperManager = $paperManager;
    }

    /**
     * Opens an exercise, creating a new paper or re-using an unfinished one.
     * Also check that max attempts are not reached if needed.
     *
     * @EXT\Route("", name="exercise_attempt_start")
     * @EXT\Method("POST")
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=true})
     *
     * @param Exercise $exercise
     * @param User     $user
     *
     * @return JsonResponse
     */
    public function startAction(Exercise $exercise, User $user = null)
    {
        $this->assertHasPermission('OPEN', $exercise);

        if (!$this->isAdmin($exercise) && !$this->attemptManager->canPass($exercise, $user)) {
            throw new AccessDeniedException('max attempts reached');
        }

        $paper = $this->attemptManager->startOrContinue($exercise, $user);

        return new JsonResponse($this->paperManager->serialize($paper));
    }

    /**
     * Submits answers to an Exercise.
     *
     * @EXT\Route("/{id}", name="exercise_attempt_submit")
     * @EXT\Method("PUT")
     * @EXT\ParamConverter("paper", class="UJMExoBundle:Attempt\Paper", options={"mapping": {"id": "uuid"}})
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=true})
     *
     * @param User    $user
     * @param Paper   $paper
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function submitAnswersAction(Paper $paper, User $user = null, Request $request)
    {
        $this->assertHasPermission('OPEN', $paper->getExercise());
        $this->assertHasPaperAccess($paper, $user);

        $errors = [];

        $data = $this->decodeRequestData($request);
        if (empty($data) || !is_array($data)) {
            $errors[] = [
                'path' => '',
                'message' => 'Invalid JSON data',
            ];
        } else {
            try {
                $this->attemptManager->submit($paper, $data, $request->getClientIp());
            } catch (ValidationException $e) {
                $errors = $e->getErrors();
            }
        }

        if (!empty($errors)) {
            return new JsonResponse($errors, 422);
        } else {
            return new JsonResponse(null, 204);
        }
    }

    /**
     * Flags a paper as finished.
     *
     * @EXT\Route("/{id}/end", name="exercise_attempt_finish")
     * @EXT\Method("PUT")
     * @EXT\ParamConverter("paper", class="UJMExoBundle:Attempt\Paper", options={"mapping": {"id": "uuid"}})
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=true})
     *
     * @param Paper $paper
     * @param User  $user
     *
     * @return JsonResponse
     */
    public function finishAction(Paper $paper, User $user = null)
    {
        $this->assertHasPermission('OPEN', $paper->getExercise());
        $this->assertHasPaperAccess($paper, $user);

        $this->attemptManager->end($paper, true);

        return new JsonResponse($this->paperManager->serialize($paper), 200);
    }

    /**
     * Returns the content of a question hint, and records the fact that it has
     * been consulted within the context of a given paper.
     *
     * @EXT\Route("/{id}/{questionId}/hints/{hintId}", name="exercise_attempt_hint_show")
     * @EXT\Method("GET")
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=true})
     * @EXT\ParamConverter("paper", class="UJMExoBundle:Attempt\Paper", options={"mapping": {"id": "uuid"}})
     *
     * @param Paper   $paper
     * @param string  $questionId
     * @param string  $hintId
     * @param User    $user
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function useHintAction(Paper $paper, $questionId, $hintId, User $user = null, Request $request)
    {
        $this->assertHasPermission('OPEN', $paper->getExercise());
        $this->assertHasPaperAccess($paper, $user);

        try {
            $hint = $this->attemptManager->useHint($paper, $questionId, $hintId, $request->getClientIp());
        } catch (\Exception $e) {
            return new JsonResponse([[
                'path' => '',
                'message' => $e->getMessage(),
            ]], 422);
        }

        return new JsonResponse($hint);
    }

    /**
     * Checks whether a User has access to a Paper.
     *
     * @param Paper $paper
     * @param User  $user
     *
     * @throws AccessDeniedException
     */
    private function assertHasPaperAccess(Paper $paper, User $user = null)
    {
        if (!$this->attemptManager->canUpdate($paper, $user)) {
            throw new AccessDeniedException();
        }
    }

    private function isAdmin(Exercise $exercise)
    {
        $collection = new ResourceCollection([$exercise->getResourceNode()]);

        return $this->authorization->isGranted('ADMINISTRATE', $collection);
    }

    private function assertHasPermission($permission, Exercise $exercise)
    {
        $collection = new ResourceCollection([$exercise->getResourceNode()]);

        if (!$this->authorization->isGranted($permission, $collection)) {
            throw new AccessDeniedException($collection->getErrorsForDisplay());
        }
    }
}
