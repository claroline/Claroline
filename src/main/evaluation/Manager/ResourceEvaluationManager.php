<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\EvaluationBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Log\Connection\LogConnectResource;
use Claroline\CoreBundle\Entity\Resource\ResourceEvaluation;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation;
use Claroline\CoreBundle\Entity\User;
use Claroline\EvaluationBundle\Entity\AbstractEvaluation;
use Claroline\EvaluationBundle\Event\EvaluationEvents;
use Claroline\EvaluationBundle\Event\ResourceEvaluationEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ResourceEvaluationManager extends AbstractEvaluationManager
{
    /** @var EventDispatcherInterface */
    private $eventDispatcher;
    /** @var ObjectManager */
    private $om;

    public function __construct(EventDispatcherInterface $eventDispatcher, ObjectManager $om)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->om = $om;
    }

    public function getUserEvaluation(ResourceNode $node, User $user, ?bool $withCreation = true)
    {
        $evaluation = $this->om->getRepository(ResourceUserEvaluation::class)->findOneBy([
            'resourceNode' => $node,
            'user' => $user,
        ]);

        if ($withCreation && empty($evaluation)) {
            $evaluation = new ResourceUserEvaluation();

            $evaluation->setResourceNode($node);
            $evaluation->setUser($user);
            $evaluation->setUserName($user->getFullName());

            $this->om->persist($evaluation);
            $this->om->flush();
        }

        return $evaluation;
    }

    public function createResourceEvaluation(ResourceNode $node, User $user, ?array $data = [], ?\DateTime $date = null): ResourceEvaluation
    {
        $resourceUserEvaluation = $this->getUserEvaluation($node, $user);

        $evaluation = new ResourceEvaluation();
        $evaluation->setResourceUserEvaluation($resourceUserEvaluation);

        $resourceUserEvaluation->setNbAttempts($resourceUserEvaluation->getNbAttempts() + 1);
        $this->om->persist($resourceUserEvaluation);

        $this->updateResourceEvaluation($evaluation, $data, $date);

        return $evaluation;
    }

    public function updateResourceEvaluation(ResourceEvaluation $attempt, ?array $data = [], ?\DateTime $date = null): ResourceEvaluation
    {
        $attempt->setDate($date ?? new \DateTime());

        if (isset($data['status'])) {
            $attempt->setStatus($data['status']);
        }
        if (isset($data['score'])) {
            $attempt->setScore($data['score']);
        }
        if (isset($data['scoreMin'])) {
            $attempt->setScoreMin($data['scoreMin']);
        }
        if (isset($data['scoreMax'])) {
            $attempt->setScoreMax($data['scoreMax']);
        }
        if (isset($data['progression'])) {
            $attempt->setProgression($data['progression']);
        }
        if (isset($data['progressionMax'])) {
            $attempt->setProgressionMax($data['progressionMax']);
        }
        if (isset($data['duration'])) {
            $attempt->setDuration($data['duration']);
        }
        if (isset($data['comment'])) {
            $attempt->setComment($data['comment']);
        }
        if (isset($data['data'])) {
            $attempt->setData($data['data']);
        }

        $resourceUserEvaluation = $this->updateEvaluation($attempt->getResourceUserEvaluation(), $data, $attempt->getDate());

        $this->om->persist($resourceUserEvaluation);
        $this->om->persist($attempt);
        $this->om->flush();

        $this->eventDispatcher->dispatch(new ResourceEvaluationEvent($resourceUserEvaluation, $attempt), EvaluationEvents::RESOURCE);

        return $attempt;
    }

    public function updateUserEvaluation(ResourceNode $node, User $user, ?array $data = [], ?\DateTime $date = null): ResourceUserEvaluation
    {
        $evaluation = $this->getUserEvaluation($node, $user);

        $this->updateEvaluation($evaluation, $data, $date);

        if (isset($data['status']) && AbstractEvaluation::STATUS_OPENED === $data['status']) {
            $evaluation->setNbOpenings($evaluation->getNbOpenings() + 1);
        }

        $this->om->persist($evaluation);
        $this->om->flush();

        $this->eventDispatcher->dispatch(new ResourceEvaluationEvent($evaluation), EvaluationEvents::RESOURCE);

        return $evaluation;
    }

    /**
     * Add duration to a resource user evaluation.
     */
    public function addDurationToResourceEvaluation(ResourceNode $node, User $user, int $duration)
    {
        $this->om->startFlushSuite();

        $resUserEval = $this->getUserEvaluation($node, $user);

        $evaluationDuration = $resUserEval->getDuration();
        if (is_null($resUserEval->getDuration())) {
            $evaluationDuration = $this->computeDuration($resUserEval);
        }

        $resUserEval->setDuration($evaluationDuration + $duration);

        $this->om->persist($resUserEval);
        $this->om->flush();

        $this->om->endFlushSuite();
    }

    /**
     * Compute duration for a resource user evaluation.
     */
    public function computeDuration(ResourceUserEvaluation $resUserEval): int
    {
        /** @var LogConnectResource[] $resourceLogs */
        $resourceLogs = $this->om->getRepository(LogConnectResource::class)->findBy([
            'resource' => $resUserEval->getResourceNode(),
            'user' => $resUserEval->getUser(),
        ]);

        $duration = 0;
        foreach ($resourceLogs as $log) {
            if ($log->getDuration()) {
                $duration += $log->getDuration();
            }
        }

        $resUserEval->setDuration($duration);

        $this->om->persist($resUserEval);
        $this->om->flush();

        return $duration;
    }
}
