<?php

namespace UJM\ExoBundle\Listener\Resource;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\Resource\DeleteResourceEvent;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\CoreBundle\Security\Collection\ResourceCollection;
use Claroline\EvaluationBundle\Manager\ResourceEvaluationManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Library\Options\Transfer;
use UJM\ExoBundle\Manager\Attempt\PaperManager;
use UJM\ExoBundle\Manager\AttemptManager;
use UJM\ExoBundle\Manager\ExerciseManager;

/**
 * Listens to resource events dispatched by the core.
 */
class ExerciseListener
{
    /** @var AuthorizationCheckerInterface */
    private $authorization;

    /** @var ExerciseManager */
    private $exerciseManager;

    /** @var PaperManager */
    private $paperManager;

    /** @var AttemptManager */
    private $attemptManager;

    /** @var ObjectManager */
    private $om;

    /** @var ResourceEvaluationManager */
    private $resourceEvalManager;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var SerializerProvider */
    private $serializer;

    /**
     * ExerciseListener constructor.
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        ExerciseManager $exerciseManager,
        PaperManager $paperManager,
        AttemptManager $attemptManager,
        ObjectManager $om,
        ResourceEvaluationManager $resourceEvalManager,
        TokenStorageInterface $tokenStorage,
        SerializerProvider $serializer
    ) {
        $this->authorization = $authorization;
        $this->exerciseManager = $exerciseManager;
        $this->paperManager = $paperManager;
        $this->attemptManager = $attemptManager;
        $this->om = $om;
        $this->resourceEvalManager = $resourceEvalManager;
        $this->tokenStorage = $tokenStorage;
        $this->serializer = $serializer;
    }

    /**
     * Loads the Exercise resource.
     */
    public function onLoad(LoadResourceEvent $event)
    {
        /** @var Exercise $exercise */
        $exercise = $event->getResource();
        $currentUser = $this->tokenStorage->getToken()->getUser();

        $canEdit = $this->authorization->isGranted('EDIT', new ResourceCollection([$exercise->getResourceNode()]));

        $options = [];
        if ($canEdit || $exercise->hasStatistics()) {
            $options[] = Transfer::INCLUDE_SOLUTIONS;
        }

        // fetch additional user data
        $lastAttempt = null;
        $nbUserPapers = 0;
        $nbUserPapersDayCount = 0;
        $userEvaluation = null;
        if ($currentUser instanceof User) {
            $lastAttempt = $this->attemptManager->getLastPaper($exercise, $currentUser);

            $nbUserPapers = (int) $this->paperManager->countUserFinishedPapers($exercise, $currentUser);
            $nbUserPapersDayCount = (int) $this->paperManager->countUserFinishedDayPapers($exercise, $currentUser);
            $userEvaluation = $this->serializer->serialize(
                $this->resourceEvalManager->getUserEvaluation($exercise->getResourceNode(), $currentUser),
                [Options::SERIALIZE_MINIMAL]
            );
        }

        $event->setData([
            'quiz' => $this->serializer->serialize($exercise, $options),
            'paperCount' => (int) $this->paperManager->countExercisePapers($exercise),

            // user data
            'lastAttempt' => $lastAttempt ? $this->paperManager->serialize($lastAttempt) : null,
            'userPaperCount' => $nbUserPapers,
            'userPaperDayCount' => $nbUserPapersDayCount,
            'userEvaluation' => $userEvaluation,
        ]);
        $event->stopPropagation();
    }

    /**
     * Deletes an Exercise resource.
     */
    public function onDelete(DeleteResourceEvent $event)
    {
        /** @var Exercise $exercise */
        $exercise = $event->getResource();

        $deletable = $this->exerciseManager->isDeletable($exercise);
        if (!$deletable) {
            // If papers, the Exercise is not completely removed
            $event->enableSoftDelete();
        }

        $event->stopPropagation();
    }
}
