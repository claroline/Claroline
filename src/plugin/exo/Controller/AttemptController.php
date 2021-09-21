<?php

namespace UJM\ExoBundle\Controller;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Security\Collection\ResourceCollection;
use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use Claroline\EvaluationBundle\Manager\ResourceEvaluationManager;
use Claroline\EvaluationBundle\Serializer\ResourceUserEvaluationSerializer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use UJM\ExoBundle\Entity\Attempt\Paper;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Manager\Attempt\PaperManager;
use UJM\ExoBundle\Manager\AttemptManager;

/**
 * Attempt Controller.
 *
 * @Route("/exercises/{exerciseId}/attempts")
 * @EXT\ParamConverter("exercise", class="UJMExoBundle:Exercise", options={"mapping": {"exerciseId": "uuid"}})
 */
class AttemptController
{
    use RequestDecoderTrait;

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
     * @var ResourceEvaluationManager
     */
    private $resourceEvalManager;

    /**
     * @var ResourceUserEvaluationSerializer
     */
    private $userEvalSerializer;

    /**
     * AttemptController constructor.
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        AttemptManager $attemptManager,
        PaperManager $paperManager,
        ResourceEvaluationManager $resourceEvalManager,
        ResourceUserEvaluationSerializer $userEvalSerializer
    ) {
        $this->authorization = $authorization;
        $this->attemptManager = $attemptManager;
        $this->paperManager = $paperManager;
        $this->resourceEvalManager = $resourceEvalManager;
        $this->userEvalSerializer = $userEvalSerializer;
    }

    /**
     * Opens an exercise, creating a new paper or re-using an unfinished one.
     * Also check that max attempts are not reached if needed.
     *
     * @Route("", name="exercise_attempt_start", methods={"POST"})
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=true})
     *
     * @param User $user
     *
     * @return JsonResponse
     */
    public function startAction(Exercise $exercise, User $user = null)
    {
        $this->assertHasPermission('OPEN', $exercise);

        if (!$this->isAdmin($exercise) && !$this->attemptManager->canPass($exercise, $user)) {
            return new JsonResponse([
                'message' => $exercise->getAttemptsReachedMessage(),
                'accessErrors' => $this->attemptManager->getErrors($exercise, $user),
                'lastAttempt' => $this->paperManager->serialize(
                    $this->attemptManager->getLastPaper($exercise, $user)
                ),
            ], 403);
        }

        return new JsonResponse($this->paperManager->serialize(
            $this->attemptManager->startOrContinue($exercise, $user)
        ));
    }

    /**
     * Submits answers to an Exercise.
     *
     * @Route("/{id}", name="exercise_attempt_submit", methods={"PUT"})
     * @EXT\ParamConverter("paper", class="UJMExoBundle:Attempt\Paper", options={"mapping": {"id": "uuid"}})
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=true})
     *
     * @param User $user
     *
     * @return JsonResponse
     */
    public function submitAnswersAction(Paper $paper, User $user = null, Request $request)
    {
        $this->assertHasPermission('OPEN', $paper->getExercise());
        $this->assertHasPaperAccess($paper, $user);

        $errors = [];

        $data = $this->decodeRequest($request);

        if (empty($data) || !is_array($data)) {
            $errors[] = [
                'path' => '',
                'message' => 'Invalid JSON data',
            ];
        } else {
            try {
                $this->attemptManager->submit($paper, $data, $request->getClientIp());
            } catch (InvalidDataException $e) {
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
     * @Route("/{id}/end", name="exercise_attempt_finish", methods={"PUT"})
     * @EXT\ParamConverter("paper", class="UJMExoBundle:Attempt\Paper", options={"mapping": {"id": "uuid"}})
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=true})
     *
     * @param User $user
     *
     * @return JsonResponse
     */
    public function finishAction(Paper $paper, User $user = null)
    {
        $this->assertHasPermission('OPEN', $paper->getExercise());
        $this->assertHasPaperAccess($paper, $user);

        $this->attemptManager->end($paper, true, !empty($user));
        $userEvaluation = !empty($user) ?
            $this->resourceEvalManager->getUserEvaluation($paper->getExercise()->getResourceNode(), $user) :
            null;

        return new JsonResponse([
            'paper' => $this->paperManager->serialize($paper),
            'userEvaluation' => !empty($userEvaluation) ? $this->userEvalSerializer->serialize($userEvaluation, [Options::SERIALIZE_MINIMAL]) : null,
        ]);
    }

    /**
     * Returns the content of a question hint, and records the fact that it has
     * been consulted within the context of a given paper.
     *
     * @Route("/{id}/{questionId}/hints/{hintId}", name="exercise_attempt_hint_show", methods={"GET"})
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=true})
     * @EXT\ParamConverter("paper", class="UJMExoBundle:Attempt\Paper", options={"mapping": {"id": "uuid"}})
     *
     * @param string $questionId
     * @param string $hintId
     * @param User   $user
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
     * @param User $user
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
