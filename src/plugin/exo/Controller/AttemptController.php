<?php

namespace UJM\ExoBundle\Controller;

use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Exception;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Security\Collection\ResourceCollection;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use Claroline\EvaluationBundle\Manager\ResourceEvaluationManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use UJM\ExoBundle\Entity\Attempt\Paper;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Manager\Attempt\PaperManager;
use UJM\ExoBundle\Manager\AttemptManager;

#[Route(path: '/exercises/{exerciseId}/attempts')]
class AttemptController
{
    use PermissionCheckerTrait;
    use RequestDecoderTrait;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        private readonly SerializerProvider $serializer,
        private readonly AttemptManager $attemptManager,
        private readonly PaperManager $paperManager,
        private readonly ResourceEvaluationManager $resourceEvalManager
    ) {
        $this->authorization = $authorization;
    }

    /**
     * Opens an exercise, creating a new paper or re-using an unfinished one.
     * Also check that max attempts are not reached if needed.
     *
     */
    #[Route(path: '', name: 'exercise_attempt_start', methods: ['POST'])]
    public function startAction(#[MapEntity(mapping: ['exerciseId' => 'uuid'])] Exercise $exercise, #[CurrentUser] ?User $user = null): JsonResponse
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
     */
    #[Route(path: '/{id}', name: 'exercise_attempt_submit', methods: ['PUT'])]
    public function submitAnswersAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Paper $paper,
        Request $request,
        #[CurrentUser] ?User $user = null
    ): JsonResponse {
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
     */
    #[Route(path: '/{id}/end', name: 'exercise_attempt_finish', methods: ['PUT'])]
    public function finishAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Paper $paper,
        #[CurrentUser] ?User $user = null
    ): JsonResponse {
        $this->assertHasPermission('OPEN', $paper->getExercise());
        $this->assertHasPaperAccess($paper, $user);

        $attempt = $this->attemptManager->end($paper, true, !empty($user));
        $userEvaluation = !empty($user) ?
            $this->resourceEvalManager->getUserEvaluation($paper->getExercise()->getResourceNode(), $user) :
            null;

        return new JsonResponse([
            'paper' => $this->paperManager->serialize($paper),
            // return the Claroline evaluations (current attempt and updated evaluation)
            'attempt' => !empty($attempt) ? $this->serializer->serialize($attempt, [SerializerInterface::SERIALIZE_MINIMAL]) : null,
            'userEvaluation' => !empty($userEvaluation) ? $this->serializer->serialize($userEvaluation, [SerializerInterface::SERIALIZE_MINIMAL]) : null,
        ]);
    }

    /**
     * Returns the content of a question hint, and records the fact that it has
     * been consulted within the context of a given paper.
     *
     */
    #[Route(path: '/{id}/{questionId}/hints/{hintId}', name: 'exercise_attempt_hint_show', methods: ['GET'])]
    public function useHintAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Paper $paper,
        string $questionId,
        string $hintId,
        Request $request,
        #[CurrentUser] ?User $user = null
    ): JsonResponse {
        $this->assertHasPermission('OPEN', $paper->getExercise());
        $this->assertHasPaperAccess($paper, $user);

        try {
            $hint = $this->attemptManager->useHint($paper, $questionId, $hintId, $request->getClientIp());
        } catch (Exception $e) {
            return new JsonResponse([[
                'path' => '',
                'message' => $e->getMessage(),
            ]], 422);
        }

        return new JsonResponse($hint);
    }

    #[Route(path: '/{id}/attemtps', name: 'exercise_attempt_give', methods: ['PUT'])]
    public function giveAttemptAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Paper $paper
    ): JsonResponse {
        $attempt = $this->attemptManager->getAttempt($paper);

        if ($attempt) {
            $this->checkPermission('ADMINISTRATE', $attempt->getResourceUserEvaluation());

            $this->resourceEvalManager->giveAnotherAttempt($attempt->getResourceUserEvaluation());
        }

        return new JsonResponse($this->paperManager->serialize($paper));
    }

    /**
     * Checks whether a User has access to a Paper.
     */
    private function assertHasPaperAccess(Paper $paper, User $user = null): void
    {
        if (!$this->attemptManager->canUpdate($paper, $user)) {
            throw new AccessDeniedException();
        }
    }

    private function isAdmin(Exercise $exercise): bool
    {
        $collection = new ResourceCollection([$exercise->getResourceNode()]);

        return $this->authorization->isGranted('ADMINISTRATE', $collection);
    }

    private function assertHasPermission($permission, Exercise $exercise): void
    {
        $collection = new ResourceCollection([$exercise->getResourceNode()]);

        if (!$this->authorization->isGranted($permission, $collection)) {
            throw new AccessDeniedException($collection->getErrorsForDisplay());
        }
    }
}
