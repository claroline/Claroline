<?php

namespace Claroline\EvaluationBundle\Manager;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\AuthenticationBundle\Messenger\Stamp\AuthenticationStamp;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\EvaluationBundle\Entity\Sequence\Requirement;
use Claroline\EvaluationBundle\Entity\Sequence\Sequence;
use Claroline\EvaluationBundle\Entity\Sequence\Step;
use Claroline\EvaluationBundle\Entity\UserEvaluation\ResourceEvaluation;
use Claroline\EvaluationBundle\Entity\UserEvaluation\SequenceEvaluation;
use Claroline\EvaluationBundle\Entity\UserEvaluation\SequenceProgression;
use Claroline\EvaluationBundle\Event\EvaluationEvents;
use Claroline\EvaluationBundle\Event\SequenceEvaluationEvent;
use Claroline\EvaluationBundle\Library\Checker\MaxFailedChecker;
use Claroline\EvaluationBundle\Library\Checker\MinSuccessChecker;
use Claroline\EvaluationBundle\Library\Checker\ProgressionChecker;
use Claroline\EvaluationBundle\Library\Checker\ScoreChecker;
use Claroline\EvaluationBundle\Library\EvaluationAggregator;
use Claroline\EvaluationBundle\Library\EvaluationStatus;
use Claroline\EvaluationBundle\Library\GenericEvaluation;
use Claroline\EvaluationBundle\Messenger\Message\InitializeSequenceEvaluations;
use Claroline\EvaluationBundle\Messenger\Message\PurgeSequenceEvaluations;
use Claroline\EvaluationBundle\Messenger\Message\RecomputeSequenceEvaluations;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class SequenceEvaluationManager extends AbstractEvaluationManager
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly MessageBusInterface $messageBus,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly ObjectManager $om,
        private readonly SerializerProvider $serializer,
        private readonly ResourceEvaluationManager $resourceEvaluationManager
    ) {
    }

    public function fulfillRequirements(Sequence $sequence, User $user): bool
    {
        /** @var Requirement[] $requirements */
        $requirements = $sequence->getRequirements()->toArray();
        if (empty($requirements)) {
            return true;
        }

        foreach ($requirements as $requirement) {
            /** @var SequenceEvaluation $userEvaluation */
            $userEvaluation = $this->om->getRepository(SequenceEvaluation::class)->findOneBy([
                'sequence' => $requirement->getRequiredSequence(),
                'user' => $user,
            ]);

            if (empty($userEvaluation)) {
                return false;
            }

            if (
                ($requirement->getStatus() && $userEvaluation->getStatus() !== $requirement->getStatus())
                || ($requirement->getProgression() && $userEvaluation->getProgression() < $requirement->getProgression())
                || ($requirement->getMinScore() && $userEvaluation->getRelativeScore() < $requirement->getMinScore())
                || ($requirement->getMaxScore() && $userEvaluation->getRelativeScore() >= $requirement->getMaxScore())
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return Sequence[]
     */
    public function getByResource(ResourceNode $resourceNode): array
    {
        return $this->om->getRepository(Sequence::class)->findByResource($resourceNode);
    }

    /**
     * @return Sequence[]
     */
    public function getByResourceAndUser(ResourceNode $resourceNode, User $user): array
    {
        return $this->om->getRepository(Sequence::class)->findByResourceAndUser($resourceNode, $user);
    }

    /**
     * Retrieve or create evaluation for a sequence and a user.
     */
    public function getUserEvaluation(Sequence $sequence, User $user, ?bool $withCreation = true): ?SequenceEvaluation
    {
        $evaluation = $this->om->getRepository(SequenceEvaluation::class)->findOneBy([
            'sequence' => $sequence,
            'user' => $user,
            'archived' => false,
        ]);

        if ($withCreation && empty($evaluation)) {
            $evaluation = new SequenceEvaluation();
            $evaluation->setSequence($sequence);
            $evaluation->setUser($user);

            $this->om->persist($evaluation);
            $this->om->flush();
        }

        return $evaluation;
    }

    public function getUserProgression(SequenceEvaluation $evaluation, ?array $options = []): array
    {
        $sequence = $evaluation->getSequence();

        $progression = [];
        foreach ($sequence->getRootSteps() as $step) {
            $progression = array_merge($progression, $this->getStepProgression($evaluation, $step, $options));
        }

        return $progression;
    }

    public function getRequiredResources(Sequence $sequence): array
    {
        $requiredResources = [];
        foreach ($sequence->getSteps() as $step) {
            if (!empty($step->getResource()) && $step->isRequired()) {
                $requiredResources[] = $step->getResource();
            }
        }

        return $requiredResources;
    }

    public function updateUserEvaluation(SequenceEvaluation $evaluation, Step $step, ?ResourceEvaluation $resourceEvaluation = null): SequenceProgression
    {
        $user = $evaluation->getUser();

        // Retrieve the current progression for this step
        $progression = $evaluation->getStepProgression($step->getUuid());
        if (empty($progression)) {
            $progression = new SequenceProgression();
            $progression->setStep($step);
            $progression->setUser($user);
        }

        $evaluation->addStepProgression($progression);
        $progression->setLastActivityAt(new \DateTime());

        if (!empty($resourceEvaluation)) {
            $progression->setResourceEvaluation($resourceEvaluation);
        }

        $recompute = false;
        if (!empty($resourceEvaluation) && ($step->isRequired() || $step->isScored())) {
            $recompute = true;
        } elseif (EvaluationStatus::COMPLETED !== $progression->getStatus()) {
            $progression->setStatus(EvaluationStatus::COMPLETED);
            $recompute = true;
        }

        $this->om->persist($progression);
        $this->om->flush();

        if ($recompute) {
            // recompute sequence evaluation
            $this->refreshEvaluation($evaluation);
        }

        return $progression;
    }

    public function refreshEvaluation(SequenceEvaluation $evaluation): void
    {
        $sequence = $evaluation->getSequence();

        $conditionCheckers = [
            new ProgressionChecker(),
        ];

        // get the success condition of the sequence if any
        $successCondition = $sequence->getSuccessCondition();
        if (!empty($successCondition)) {
            if (array_key_exists('score', $successCondition) && is_numeric($successCondition['score'])) {
                // check user score (the condition is a percentage of the max score)
                $conditionCheckers[] = new ScoreChecker($successCondition['score']);
            }

            if (array_key_exists('minSuccess', $successCondition) && is_numeric($successCondition['minSuccess'])) {
                // check user success resources
                $conditionCheckers[] = new MinSuccessChecker($successCondition['minSuccess']);
            }

            if (array_key_exists('maxFailed', $successCondition) && is_numeric($successCondition['maxFailed'])) {
                // check user failed resources
                $conditionCheckers[] = new MaxFailedChecker($successCondition['maxFailed']);
            }
        }

        // the sequence evaluation aggregates the progression/score of all its steps and required resources
        $aggregator = new EvaluationAggregator($conditionCheckers);

        foreach ($sequence->getSteps() as $step) {
            $stepProgression = $evaluation->getStepProgression($step->getUuid());
            if (!empty($step->getResource()) && $step->isRequired()) {
                // the step contains a required resource, we need to get the evaluation for this resource
                // to compute the step progression
                if (!empty($stepProgression) && !empty($stepProgression->getResourceEvaluation())) {
                    $resourceEvaluation = $stepProgression->getResourceEvaluation();
                } else {
                    // no evaluation, adds an empty evaluation for correct progression check
                    $resourceEvaluation = new GenericEvaluation(0);
                }

                $aggregator->addEvaluation($resourceEvaluation, $step->isScored());
            } else {
                // no required resource in the step, we only check if the step is seen/done
                $stepDone = !empty($stepProgression) && EvaluationStatus::COMPLETED === $stepProgression->getStatus();
                $aggregator->addEvaluation(new GenericEvaluation($stepDone ? 100 : 0));
            }
        }

        // update evaluation data
        $hasChanged = $this->updateEvaluation($evaluation, [
            'status' => $aggregator->getStatus(),
            'score' => $aggregator->getScore(),
            'scoreMax' => $aggregator->getScoreMax(),
            'progression' => $aggregator->getProgression(),
        ]);

        $this->om->persist($evaluation);
        $this->om->flush();

        if ($hasChanged['status'] || $hasChanged['progression'] || $hasChanged['score']) {
            $this->eventDispatcher->dispatch(new SequenceEvaluationEvent($evaluation, $hasChanged), EvaluationEvents::SEQUENCE_EVALUATION);
        }
    }

    public function initializeEvaluations(Sequence $sequence): void
    {
        $userIds = $this->om->getRepository(Sequence::class)->findRequiredUserIds($sequence);
        if (!empty($userIds)) {
            $this->messageBus->dispatch(
                new InitializeSequenceEvaluations($sequence->getId(), $userIds),
                [new AuthenticationStamp($this->tokenStorage->getToken()?->getUser()->getId())]
            );
        }
    }

    public function archiveEvaluation(SequenceEvaluation $evaluation): void
    {
        $this->om->startFlushSuite();

        $evaluation->setArchived(true);
        $evaluation->setArchivedAt(new \DateTime());

        $this->om->persist($evaluation);

        foreach ($evaluation->getStepProgressions() as $stepProgression) {
            if ($stepProgression->getResourceEvaluation()) {
                $this->resourceEvaluationManager->archiveEvaluation($stepProgression->getResourceEvaluation());
            }
        }

        $this->om->endFlushSuite();
    }

    /**
     * Recomputes all the evaluations of a sequence.
     * This is called when required resources are added/removed to update users progression and score.
     * This can also be called manually from the ui.
     */
    public function recomputeEvaluations(Sequence $sequence): void
    {
        $this->messageBus->dispatch(
            new RecomputeSequenceEvaluations($sequence->getId()),
            [new AuthenticationStamp($this->tokenStorage->getToken()?->getUser()->getId())]
        );
    }

    /**
     * Create missing links between the SequenceEvaluation and the evaluation for used resources
     * and then refreshes the SequenceEvaluation scores, status, etc.
     */
    public function recomputeEvaluation(SequenceEvaluation $evaluation): void
    {
        $sequence = $evaluation->getSequence();
        $user = $evaluation->getUser();
        foreach ($sequence->getSteps() as $step) {
            if ($step->getResource()) {
                $progression = $evaluation->getStepProgression($step->getUuid());
                $resourceEvaluation = $this->resourceEvaluationManager->getUserEvaluation($step->getResource(), $user, false);
                if ($resourceEvaluation) {
                    if (empty($progression)) {
                        $progression = new SequenceProgression();
                        $progression->setStep($step);
                        $progression->setUser($user);
                    }

                    $evaluation->addStepProgression($progression);
                    $progression->setLastActivityAt($resourceEvaluation->getLastActivityAt());
                    $progression->setResourceEvaluation($resourceEvaluation);

                    $this->om->persist($progression);
                }
            }
        }

        $this->refreshEvaluation($evaluation);
    }

    public function purgeEvaluations(Sequence $sequence): void
    {
        $this->messageBus->dispatch(
            new PurgeSequenceEvaluations($sequence->getId()),
            [new AuthenticationStamp($this->tokenStorage->getToken()?->getUser()->getId())]
        );
    }

    private function getStepProgression(SequenceEvaluation $evaluation, Step $step, ?array $options = []): array
    {
        $stepProgression = $evaluation->getStepProgression($step->getUuid());

        $stepEvaluation = null;
        if (!empty($stepProgression)) {
            if (!empty($step->getResource()) && $step->isRequired()) {
                if (!empty($stepProgression->getResourceEvaluation())) {
                    $stepEvaluation = $this->serializer->serialize($stepProgression->getResourceEvaluation(), $options);
                }
            } else {
                $stepEvaluation = [
                    'id' => $stepProgression->getStep()->getUuid(),
                    'status' => $stepProgression->getStatus(),
                    'lastActivityAt' => DateNormalizer::normalize($stepProgression->getLastActivityAt()),
                ];
            }
        }

        if (empty($stepEvaluation)) {
            $stepEvaluation = [
                'id' => $step->getUuid(),
                'status' => EvaluationStatus::NOT_ATTEMPTED,
            ];
        }

        $stepEvaluation['step'] = [
            'id' => $step->getUuid(),
            'name' => $step->getTitle(),
        ];

        if ($step->getChildren()->count() > 0) {
            $childrenProgression = [];
            foreach ($step->getChildren() as $child) {
                $childrenProgression = array_merge($childrenProgression, $this->getStepProgression($evaluation, $child, $options));
            }

            return array_merge([$stepEvaluation], $childrenProgression);
        }

        return [$stepEvaluation];
    }
}
