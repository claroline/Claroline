<?php

namespace UJM\ExoBundle\Controller;

use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Manager\DocimologyManager;
use UJM\ExoBundle\Manager\Item\ItemManager;

/**
 * @Route("/exercises/{id}/statistics")
 * @EXT\ParamConverter("exercise", class="UJM\ExoBundle\Entity\Exercise", options={"mapping": {"id": "uuid"}})
 */
class AnalyticsController
{
    use PermissionCheckerTrait;
    use RequestDecoderTrait;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var AuthorizationCheckerInterface */
    private $authorization;

    /** @var DocimologyManager */
    private $docimologyManager;

    /** @var ItemManager */
    private $itemManager;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorization,
        DocimologyManager $docimologyManager,
        ItemManager $itemManager
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->authorization = $authorization;
        $this->docimologyManager = $docimologyManager;
        $this->itemManager = $itemManager;
    }

    /**
     * Opens the docimology of a quiz.
     *
     * @Route("/docimology", name="exercise_statistics_docimology", methods={"GET"})
     */
    public function getDocimologyAction(Exercise $exercise): JsonResponse
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
     *
     * @Route("/answers", name="exercise_statistics", methods={"GET"})
     */
    public function getAnswersAction(Exercise $exercise): JsonResponse
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
                $statistics[$question->getUuid()] = !empty($itemStats['solutions']) ? $itemStats['solutions'] : new \stdClass();
            }
        }

        return new JsonResponse($statistics);
    }

    /**
     * @Route("/attempts", name="exercise_statistics_attempts", methods={"GET"})
     * @Route("/attempts/{userId}", name="exercise_statistics_user_attempts", methods={"GET"})
     * @EXT\ParamConverter("user", class="Claroline\CoreBundle\Entity\User", options={"mapping": {"userId": "uuid"}})
     */
    public function getAttemptsAction(Exercise $exercise, User $user = null): JsonResponse
    {
        $statsAdmin = $this->checkPermission('VIEW_DOCIMOLOGY', $exercise->getResourceNode());
        if (!$statsAdmin) {
            $this->checkPermission('OPEN', $exercise->getResourceNode(), [], true);

            if (!$exercise->hasStatistics() && 'none' === $exercise->getOverviewStats() && 'none' === $exercise->getEndStats()) {
                // stats are disabled for users in this quiz
                throw new AccessDeniedException('You cannot open statistics for this quiz.');
            }

            // only open user stats to the owner
            $currentUser = $this->tokenStorage->getToken()->getUser();
            if ($user && (!($currentUser instanceof User) || $currentUser->getId() !== $user->getId())) {
                throw new AccessDeniedException('You cannot open statistics for this quiz.');
            }
        }

        return new JsonResponse(
            $this->docimologyManager->getAttemptsScores($exercise, !$exercise->isAllPapersStatistics(), $user)
        );
    }
}
