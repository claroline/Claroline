<?php

namespace Claroline\DropZoneBundle\Manager;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CommunityBundle\Entity\Team;
use Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation;
use Claroline\CoreBundle\Entity\User;
use Claroline\DropZoneBundle\Entity\Drop;
use Claroline\DropZoneBundle\Entity\Dropzone;
use Claroline\DropZoneBundle\Repository\DropRepository;
use Claroline\EvaluationBundle\Entity\AbstractEvaluation;
use Claroline\EvaluationBundle\Manager\ResourceEvaluationManager;

/**
 * TODO : we shouldn't store the whole serialized Drop inside the ResourceAttempt.
 */
class EvaluationManager
{
    private DropRepository $dropRepo;

    public function __construct(
        private readonly ObjectManager $om,
        private readonly SerializerProvider $serializer,
        private readonly ResourceEvaluationManager $resourceEvalManager,
        private readonly DropManager $dropManager
    ) {
        $this->dropRepo = $om->getRepository(Drop::class);
    }

    public function getResourceUserEvaluation(Dropzone $dropzone, User $user): ResourceUserEvaluation
    {
        return $this->resourceEvalManager->getUserEvaluation($dropzone->getResourceNode(), $user);
    }

    /**
     * Gets user drop or creates one.
     */
    public function getUserDrop(Dropzone $dropzone, User $user, ?bool $withCreation = false): ?Drop
    {
        $drops = $this->dropRepo->findBy(['dropzone' => $dropzone, 'user' => $user, 'teamUuid' => null]);
        $drop = count($drops) > 0 ? $drops[0] : null;

        if (empty($drop) && $withCreation) {
            $drop = $this->createDrop($dropzone, $user);
        }

        return $drop;
    }

    /**
     * Gets team drop or creates one.
     */
    public function getTeamDrop(Dropzone $dropzone, Team $team, User $user, ?bool $withCreation = false): ?Drop
    {
        $drop = $this->dropRepo->findOneBy(['dropzone' => $dropzone, 'teamUuid' => $team->getUuid()]);

        if ($withCreation) {
            if (empty($drop)) {
                $drop = $this->createDrop($dropzone, $user);

                $drop->setTeamId($team->getId());
                $drop->setTeamUuid($team->getUuid());
                $drop->setTeamName($team->getName());

                foreach ($team->getRole()->getUsers() as $teamUser) {
                    $drop->addUser($teamUser);
                    $this->resourceEvalManager->createAttempt(
                        $dropzone->getResourceNode(),
                        $teamUser,
                        ['status' => AbstractEvaluation::STATUS_INCOMPLETE]
                    );
                }
            } elseif (!$drop->hasUser($user)) {
                $drop->addUser($user);

                $this->resourceEvalManager->createAttempt(
                    $dropzone->getResourceNode(),
                    $user,
                    ['status' => AbstractEvaluation::STATUS_INCOMPLETE]
                );
            }

            $this->om->persist($drop);
            $this->om->flush();
        }

        return $drop;
    }

    /**
     * Computes Complete status for a user.
     *
     * (I don't know why drop is optional)
     */
    public function checkCompletion(Dropzone $dropzone, array $users, Drop $drop = null): void
    {
        $teamId = !empty($drop) ? $drop->getTeamUuid() : null;

        $this->om->startFlushSuite();

        // By default, drop is complete if teacher review is enabled or drop is unlocked for user
        $isComplete = !empty($drop) ? $drop->isFinished() && (!$dropzone->isPeerReview() || $drop->isUnlockedUser()) : false;

        // If drop is not complete by default, checks for the number of finished corrections done by user
        if (!$isComplete) {
            $expectedCorrectionTotal = $dropzone->getExpectedCorrectionTotal();
            $finishedPeerDrops = $this->dropManager->getFinishedPeerDrops($dropzone, $users[0], $teamId);
            $isComplete = count($finishedPeerDrops) >= $expectedCorrectionTotal;
        }

        if ($isComplete) {
            foreach ($users as $user) {
                $userEval = $this->resourceEvalManager->getUserEvaluation($dropzone->getResourceNode(), $user, false);

                if (!empty($userEval) && !$userEval->isTerminated()) {
                    $this->resourceEvalManager->createAttempt(
                        $dropzone->getResourceNode(),
                        $user,
                        ['status' => AbstractEvaluation::STATUS_COMPLETED, 'progression' => 100]
                    );
                } elseif (!empty($drop)) {
                    $this->updateDropProgression($dropzone, $drop, 100);
                }
            }
        }

        $this->om->endFlushSuite();
    }

    /**
     * Computes Success status for a Drop.
     */
    public function checkSuccess(Drop $drop): void
    {
        $this->om->startFlushSuite();

        $dropzone = $drop->getDropzone();
        $users = [$drop->getUser()];

        if (Dropzone::DROP_TYPE_TEAM === $dropzone->getDropType()) {
            $users = $drop->getUsers();
        }

        $computeStatus = $drop->isFinished() && (!$dropzone->isPeerReview() || $drop->isUnlockedDrop());

        if (!$computeStatus) {
            $nbValidCorrections = 0;
            $expectedCorrectionTotal = $dropzone->getExpectedCorrectionTotal();
            $corrections = $drop->getCorrections();

            foreach ($corrections as $correction) {
                if ($correction->isFinished() && $correction->isValid()) {
                    ++$nbValidCorrections;
                }
            }
            $computeStatus = $nbValidCorrections >= $expectedCorrectionTotal;
        }

        if ($computeStatus) {
            $score = $drop->getScore();
            $scoreToPass = $dropzone->getScoreToPass();
            $scoreMax = $dropzone->getScoreMax();
            $status = !empty($scoreMax) && (($score / $scoreMax) * 100) >= $scoreToPass ?
                AbstractEvaluation::STATUS_PASSED :
                AbstractEvaluation::STATUS_FAILED;

            foreach ($users as $user) {
                $this->resourceEvalManager->createAttempt(
                    $dropzone->getResourceNode(),
                    $user,
                    [
                        'status' => $status,
                        'score' => $score,
                        'scoreMax' => $scoreMax,
                        'data' => $this->serializer->serialize($drop),
                    ]
                );
            }
        }

        $this->om->endFlushSuite();
    }

    /**
     * Updates progression of ResourceEvaluation for drop.
     */
    public function updateDropProgression(Dropzone $dropzone, Drop $drop, int $progression): void
    {
        $this->om->startFlushSuite();

        if (Dropzone::DROP_TYPE_TEAM === $dropzone->getDropType()) {
            foreach ($drop->getUsers() as $user) {
                $this->resourceEvalManager->createAttempt(
                    $dropzone->getResourceNode(),
                    $user,
                    ['progression' => $progression, 'data' => $this->serializer->serialize($drop)]
                );
            }
        } else {
            $this->resourceEvalManager->createAttempt(
                $dropzone->getResourceNode(),
                $drop->getUser(),
                ['progression' => $progression, 'data' => $this->serializer->serialize($drop)]
            );
        }

        $this->om->endFlushSuite();
    }

    /**
     * Computes Drop score from submitted Corrections.
     */
    public function computeDropScore(Drop $drop): Drop
    {
        $corrections = $drop->getCorrections();
        $score = 0;
        $nbValidCorrection = 0;

        foreach ($corrections as $correction) {
            if ($correction->isFinished() && $correction->isValid()) {
                $score += $correction->getScore();
                ++$nbValidCorrection;
            }
        }

        $score = $nbValidCorrection > 0 ? round($score / $nbValidCorrection, 2) : null;
        $drop->setScore($score);

        $this->om->persist($drop);
        $this->om->flush();

        return $drop;
    }

    /**
     * @deprecated use crud instead
     */
    private function createDrop(Dropzone $dropzone, User $user): Drop
    {
        $this->om->startFlushSuite();

        $drop = new Drop();
        $drop->setUser($user);
        $drop->setDropzone($dropzone);

        $this->om->persist($drop);

        $this->resourceEvalManager->createAttempt(
            $dropzone->getResourceNode(),
            $user,
            ['status' => AbstractEvaluation::STATUS_INCOMPLETE]
        );

        $this->om->endFlushSuite();

        return $drop;
    }
}
