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
use Claroline\AuthenticationBundle\Messenger\Stamp\AuthenticationStamp;
use Claroline\CoreBundle\Component\Resource\ResourceProvider;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\EvaluationBundle\Component\Resource\EvaluatedResourceInterface;
use Claroline\EvaluationBundle\Entity\Parameters\ResourceParameters;
use Claroline\EvaluationBundle\Entity\UserEvaluation\ResourceAttempt;
use Claroline\EvaluationBundle\Entity\UserEvaluation\ResourceEvaluation;
use Claroline\EvaluationBundle\Event\EvaluationEvents;
use Claroline\EvaluationBundle\Event\ResourceAttemptEvent;
use Claroline\EvaluationBundle\Event\ResourceEvaluationEvent;
use Claroline\EvaluationBundle\Messenger\Message\PurgeResourceEvaluations;
use Claroline\EvaluationBundle\Messenger\Message\RecomputeResourceEvaluations;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ResourceEvaluationManager extends AbstractEvaluationManager
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly MessageBusInterface $messageBus,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly ObjectManager $om,
        private readonly ResourceProvider $resourceProvider
    ) {
    }

    public function supportsEvaluation(ResourceNode $resourceNode): bool
    {
        $resourceHandler = $this->resourceProvider->getComponent($resourceNode->getResourceType()->getName());

        return $resourceHandler instanceof EvaluatedResourceInterface;
    }

    public function supportsAttempts(ResourceNode $resourceNode): bool
    {
        $resourceHandler = $this->resourceProvider->getComponent($resourceNode->getResourceType()->getName());

        return $resourceHandler instanceof EvaluatedResourceInterface && $resourceHandler::supportsAttempts();
    }

    /**
     * Check if evaluation is enabled for a resource.
     */
    public function isEvaluated(ResourceNode $resourceNode): bool
    {
        if (!$this->supportsEvaluation($resourceNode)) {
            return false;
        }

        return 1 === $this->om->getRepository(ResourceParameters::class)->count(['resource' => $resourceNode]);
    }

    /**
     * Get the evaluation parameters for a resource.
     */
    public function getParameters(ResourceNode $resourceNode): ?ResourceParameters
    {
        if (!$this->supportsEvaluation($resourceNode)) {
            return null;
        }

        return $this->om->getRepository(ResourceParameters::class)->findOneBy(['resource' => $resourceNode]);
    }

    public function getUserEvaluation(ResourceNode $node, User $user, ?bool $withCreation = true): ?ResourceEvaluation
    {
        if (!$this->isEvaluated($node)) {
            // evaluation is not enabled for this resource, the current user will have no evaluation data
            return null;
        }

        $evaluation = $this->om->getRepository(ResourceEvaluation::class)->findOneBy([
            'resourceNode' => $node,
            'user' => $user,
            'archived' => false,
        ]);

        if ($withCreation && empty($evaluation)) {
            $evaluation = new ResourceEvaluation();
            $evaluation->setResourceNode($node);
            $evaluation->setUser($user);

            $this->om->persist($evaluation);
            $this->om->flush();
        }

        return $evaluation;
    }

    public function createAttempt(ResourceNode $node, User $user, ?array $data = [], \DateTimeInterface $date = null): ResourceAttempt
    {
        // retrieve the parent evaluation for the attempt
        $evaluation = $this->getUserEvaluation($node, $user);

        // initialize a new attempt
        $attempt = new ResourceAttempt();
        $attempt->setResourceUserEvaluation($evaluation);
        $this->om->persist($attempt);

        $evaluation->setNbAttempts($evaluation->getNbAttempts() + 1);

        $this->updateAttempt($attempt, $data, $date);

        return $attempt;
    }

    public function updateAttempt(ResourceAttempt $attempt, ?array $data = [], \DateTimeInterface $date = null): ResourceAttempt
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
        $evaluationUpdated = $this->updateEvaluation($attempt->getResourceUserEvaluation(), $data, $attempt->getLastActivityAt());

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

    public function updateUserEvaluation(ResourceNode $node, User $user, ?array $data = [], \DateTimeInterface $date = null): ?ResourceEvaluation
    {
        $this->om->startFlushSuite();

        $evaluation = $this->getUserEvaluation($node, $user);
        if (empty($evaluation)) {
            return null;
        }

        $evaluationUpdated = $this->updateEvaluation($evaluation, $data, $date);

        $this->om->endFlushSuite();

        if ($evaluationUpdated['status'] || $evaluationUpdated['progression'] || $evaluationUpdated['score']) {
            $this->eventDispatcher->dispatch(new ResourceEvaluationEvent($evaluation, $evaluationUpdated), EvaluationEvents::RESOURCE_EVALUATION);
        }

        return $evaluation;
    }

    public function refreshEvaluation(ResourceEvaluation $evaluation): void
    {
    }

    /**
     * Gives another attempt to a user.
     * This allows the user to redo an attempt event if a has reached the max attempts allowed by the resource.
     * NB. This is only implemented in the quiz plugin for now.
     */
    public function giveAnotherAttempt(ResourceEvaluation $evaluation): void
    {
        if (!$this->supportsAttempts($evaluation->getResourceNode())) {
            return;
        }

        if (0 !== $evaluation->getNbAttempts()) {
            $evaluation->setNbAttempts($evaluation->getNbAttempts() - 1);

            $this->om->persist($evaluation);
            $this->om->flush();

            $this->eventDispatcher->dispatch(new ResourceEvaluationEvent($evaluation, ['nbAttempts' => true]), EvaluationEvents::RESOURCE_EVALUATION);
        }
    }

    public function archiveEvaluation(ResourceEvaluation $evaluation): void
    {
        $this->om->startFlushSuite();

        $evaluation->setArchived(true);
        $evaluation->setArchivedAt(new \DateTime());

        $this->om->persist($evaluation);

        if ($this->supportsAttempts($evaluation->getResourceNode())) {
            $attempts = $this->om->getRepository(ResourceAttempt::class)->findBy(['resourceUserEvaluation' => $evaluation]);
            foreach ($attempts as $attempt) {
                $attempt->setArchived(true);
                $attempt->setArchivedAt(new \DateTime());

                $this->om->persist($attempt);
            }
        }

        $this->om->endFlushSuite();
    }

    /**
     * Recomputes all the evaluations of a resource.
     */
    public function recomputeEvaluations(ResourceNode $resourceNode): void
    {
        $this->messageBus->dispatch(
            new RecomputeResourceEvaluations($resourceNode->getId()),
            [new AuthenticationStamp($this->tokenStorage->getToken()?->getUser()->getId())]
        );
    }

    public function purgeEvaluations(ResourceNode $resourceNode): void
    {
        $this->messageBus->dispatch(
            new PurgeResourceEvaluations($resourceNode->getId()),
            [new AuthenticationStamp($this->tokenStorage->getToken()?->getUser()->getId())]
        );
    }
}
