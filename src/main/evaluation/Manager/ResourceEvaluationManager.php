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
use Claroline\EvaluationBundle\Event\ResourceAttemptEvent;
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

    public function getUserEvaluation(ResourceNode $node, User $user, ?bool $withCreation = true): ?ResourceUserEvaluation
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

    public function createAttempt(ResourceNode $node, User $user, ?array $data = [], ?\DateTime $date = null): ResourceEvaluation
    {
        // retrieve the parent evaluation for the attempt
        $evaluation = $this->getUserEvaluation($node, $user);

        // initialize a new attempt
        $attempt = new ResourceEvaluation();
        $attempt->setResourceUserEvaluation($evaluation);
        $this->om->persist($attempt);

        $evaluation->setNbAttempts($evaluation->getNbAttempts() + 1);

        $this->updateAttempt($attempt, $data, $date);

        return $attempt;
    }

    public function updateAttempt(ResourceEvaluation $attempt, ?array $data = [], ?\DateTime $date = null): ResourceEvaluation
    {
        // update the current attempt data
        $attemptUpdated = $this->updateEvaluation($attempt, $data, $date);

        if (isset($data['comment'])) {
            $attempt->setComment($data['comment']);
        }
        if (isset($data['data'])) {
            $attempt->setData($data['data']);
        }

        // update the parent evaluation
        $evaluationUpdated = $this->updateEvaluation($attempt->getResourceUserEvaluation(), $data, $attempt->getDate());

        $this->om->flush();

        if ($attemptUpdated['status'] || $attemptUpdated['progression'] || $attemptUpdated['score']) {
            // notify the app an attempt has progressed
            $this->eventDispatcher->dispatch(new ResourceAttemptEvent($attempt, $attemptUpdated), EvaluationEvents::RESOURCE_ATTEMPT);
        }

        if ($evaluationUpdated['status'] || $evaluationUpdated['progression'] || $evaluationUpdated['score']) {
            // notify the app an evaluation has progressed
            $this->eventDispatcher->dispatch(new ResourceEvaluationEvent($attempt->getResourceUserEvaluation(), $evaluationUpdated), EvaluationEvents::RESOURCE_EVALUATION);
        }

        return $attempt;
    }

    public function updateUserEvaluation(ResourceNode $node, User $user, ?array $data = [], ?\DateTime $date = null, ?bool $withCreation = true): ?ResourceUserEvaluation
    {
        $this->om->startFlushSuite();

        $evaluation = $this->getUserEvaluation($node, $user, $withCreation);
        if (empty($evaluation)) {
            return null;
        }

        $evaluationUpdated = $this->updateEvaluation($evaluation, $data, $date);

        if (isset($data['status']) && AbstractEvaluation::STATUS_OPENED === $data['status']) {
            $evaluation->setNbOpenings($evaluation->getNbOpenings() + 1);
        }

        $this->om->endFlushSuite();

        if ($evaluationUpdated['status'] || $evaluationUpdated['progression'] || $evaluationUpdated['score']) {
            $this->eventDispatcher->dispatch(new ResourceEvaluationEvent($evaluation, $evaluationUpdated), EvaluationEvents::RESOURCE_EVALUATION);
        }

        return $evaluation;
    }

    /**
     * Add duration to a resource user evaluation.
     *
     * @deprecated should not be declared here
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
     *
     * @deprecated should not be declared here
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

    /**
     * @deprecated use createAttempt()
     */
    public function createResourceEvaluation(ResourceNode $node, User $user, ?array $data = [], ?\DateTime $date = null): ResourceEvaluation
    {
        return $this->createAttempt($node, $user, $data, $date);
    }

    /**
     * @deprecated use updateAttempt()
     */
    public function updateResourceEvaluation(ResourceEvaluation $attempt, ?array $data = [], ?\DateTime $date = null): ResourceEvaluation
    {
        return $this->updateAttempt($attempt, $data, $date);
    }
}
