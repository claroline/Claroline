<?php

namespace UJM\ExoBundle\Controller;

use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use stdClass;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Manager\DocimologyManager;
use UJM\ExoBundle\Manager\Item\ItemManager;

#[Route(path: '/exercises/{id}/statistics')]
class AnalyticsController
{
    use PermissionCheckerTrait;
    use RequestDecoderTrait;

    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorization,
        private readonly DocimologyManager $docimologyManager,
        private readonly ItemManager $itemManager
    ) {
        $this->authorization = $authorization;
    }

    /**
     * Opens the docimology of a quiz.
     */
    #[Route(path: '/docimology', name: 'exercise_statistics_docimology', methods: ['GET'])]
    public function getDocimologyAction(#[MapEntity(mapping: ['id' => 'uuid'])] Exercise $exercise): JsonResponse
    {
        if (!$exercise->hasStatistics()) {
            $this->checkPermission('VIEW_DOCIMOLOGY', $exercise->getResourceNode(), [], true);
        } else {
            $this->checkPermission('OPEN', $exercise->getResourceNode(), [], true);
        }

        return new JsonResponse(
            $this->docimologyManager->getStatistics($exercise, 100)
        );
    }

    /**
     * Gets statistics of chosen answers of an Exercise.
     */
    #[Route(path: '/answers', name: 'exercise_statistics', methods: ['GET'])]
    public function getAnswersAction(#[MapEntity(mapping: ['id' => 'uuid'])] Exercise $exercise): JsonResponse
    {
        if (!$exercise->hasStatistics()) {
            $this->checkPermission('VIEW_DOCIMOLOGY', $exercise->getResourceNode(), [], true);
        } else {
            $this->checkPermission('OPEN', $exercise->getResourceNode(), [], true);
        }

        $statistics = [];
        $finishedOnly = !$exercise->isAllPapersStatistics();

        foreach ($exercise->getSteps() as $step) {
            foreach ($step->getQuestions() as $question) {
                $itemStats = $this->itemManager->getStatistics($question, $exercise, $finishedOnly);
                $statistics[$question->getUuid()] = !empty($itemStats['solutions']) ? $itemStats['solutions'] : new stdClass();
            }
        }

        return new JsonResponse($statistics);
    }

    #[Route(path: '/attempts', name: 'exercise_statistics_attempts', methods: ['GET'])]
    #[Route(path: '/attempts/{userId}', name: 'exercise_statistics_user_attempts', methods: ['GET'])]
    public function getAttemptsAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Exercise $exercise,
        #[MapEntity(mapping: ['userId' => 'uuid'])]
        User $user = null
    ): JsonResponse {
        $statsAdmin = $this->checkPermission('VIEW_DOCIMOLOGY', $exercise->getResourceNode());
        if (!$statsAdmin) {
            $this->checkPermission('OPEN', $exercise->getResourceNode(), [], true);

            if (!$exercise->hasStatistics() && 'none' === $exercise->getOverviewStats() && 'none' === $exercise->getEndStats()) {
                // stats are disabled for users in this quiz
                throw new AccessDeniedException('You cannot open statistics for this quiz.');
            }

            // only open user stats to the owner
            $currentUser = $this->tokenStorage->getToken()?->getUser();
            if ($user && (!($currentUser instanceof User) || $currentUser->getId() !== $user->getId())) {
                throw new AccessDeniedException('You cannot open statistics for this quiz.');
            }
        }

        return new JsonResponse(
            $this->docimologyManager->getAttemptsScores($exercise, !$exercise->isAllPapersStatistics(), $user)
        );
    }
}
