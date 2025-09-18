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
use Claroline\EvaluationBundle\Entity\SequenceProgression;
use Claroline\EvaluationBundle\Entity\UserEvaluation\SequenceEvaluation;
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
use Claroline\EvaluationBundle\Repository\SequenceRepository;
use Claroline\EvaluationBundle\Repository\UserEvaluation\SequenceProgressionRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class SequenceEvaluationManager extends AbstractEvaluationManager
{
    private SequenceProgressionRepository $progressionRepo;
    private SequenceRepository $sequenceRepo;

    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly MessageBusInterface $messageBus,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly ObjectManager $om,
        private readonly SerializerProvider $serializer,
        private readonly ResourceEvaluationManager $resourceEvalManager
    ) {
        $this->progressionRepo = $this->om->getRepository(SequenceProgression::class);
        $this->sequenceRepo = $this->om->getRepository(Sequence::class);
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

    public function getByResource(ResourceNode $resourceNode): array
    {
        return $this->om->getRepository(Sequence::class)->findByRequiredResource($resourceNode);
    }

    public function getByResourceAndUser(ResourceNode $resourceNode, User $user): array
    {
        return $this->om->getRepository(Sequence::class)->findByRequiredResourceAndUser($resourceNode, $user);
    }

    /**
     * Retrieve or create evaluation for a sequence and a user.
     */
    public function getUserEvaluation(Sequence $sequence, User $user, ?bool $withCreation = true): ?SequenceEvaluation
    {
        $evaluation = $this->om->getRepository(SequenceEvaluation::class)->findOneBy([
            'sequence' => $sequence,
            'user' => $user,
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

    public function getResourceEvaluations(Sequence $sequence, User $user): array
    {
        return $this->sequenceRepo->findResourceEvaluations($sequence, $user);
    }

    public function getProgression(Sequence $sequence, User $user, ?array $options = []): array
    {
        // loads all the user evaluations (step progression and resource evaluations)
        $progression = $this->progressionRepo->findBySequenceAndUser($sequence, $user);
        $resourceEvaluations = $this->getResourceEvaluations($sequence, $user);

        $progress = [];
        foreach ($sequence->getRootSteps() as $step) {
            $progress = array_merge($progress, $this->getStepProgression($step, $progression, $resourceEvaluations, $options));
        }

        return $progress;
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

    private function getStepProgression(Step $step, ?array $stepProgressions = [], ?array $resourceEvaluations = [], ?array $options = []): array
    {
        $stepEvaluation = null;
        if (!empty($step->getResource()) && $step->isRequired()) {
            $resourceId = $step->getResource()->getId();
            foreach ($resourceEvaluations as $resourceEvaluation) {
                if ($resourceEvaluation->getResourceNode()->getId() === $resourceId) {
                    $stepEvaluation = $this->serializer->serialize($resourceEvaluation, $options);
                    break;
                }
            }
        } else {
            foreach ($stepProgressions as $stepProgression) {
                if ($stepProgression->getStep()->getId() === $step->getId()) {
                    $stepEvaluation = [
                        'id' => $stepProgression->getStep()->getUuid(),
                        'status' => $stepProgression->getStatus(),
                        'lastActivityAt' => DateNormalizer::normalize($stepProgression->getLastActivityAt()),
                    ];
                    break;
                }
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
                $childrenProgression = array_merge($childrenProgression, $this->getStepProgression($child, $stepProgressions, $resourceEvaluations, $options));
            }

            return array_merge([$stepEvaluation], $childrenProgression);
        }

        return [$stepEvaluation];
    }

    /**
     * Get all steps progressions for a user.
     */
    public function getStepsProgressionForUser(Sequence $sequence, User $user): array
    {
        $progression = $this->progressionRepo->findBySequenceAndUser($sequence, $user);

        $stepsProgression = [];
        foreach ($progression as $stepProgression) {
            $stepsProgression[$stepProgression->getStep()->getUuid()] = $stepProgression->getStatus();
        }

        return $stepsProgression;
    }

    public function update(Step $step, User $user): SequenceProgression
    {
        // Retrieve the current progression for this step
        $progression = $this->progressionRepo->findOneBy([
            'step' => $step,
            'user' => $user,
        ]);

        if (empty($progression)) {
            $progression = new SequenceProgression();
            $progression->setStep($step);
            $progression->setUser($user);
        }

        $progression->setLastActivityAt(new \DateTime());

        $recompute = false;
        if (EvaluationStatus::COMPLETED !== $progression->getStatus()) {
            $recompute = true;
            $progression->setStatus(EvaluationStatus::COMPLETED);
        }

        $this->om->persist($progression);
        $this->om->flush();

        if ($recompute) {
            // recompute sequence progression for user
            $this->computeEvaluation($step->getSequence(), $user);
        }

        return $progression;
    }

    public function computeEvaluation(Sequence $sequence, User $user): SequenceEvaluation
    {
        $evaluation = $this->getUserEvaluation($sequence, $user);
        $this->refreshEvaluation($evaluation);

        return $evaluation;
    }

    public function refreshEvaluation(SequenceEvaluation $evaluation): void
    {
        $sequence = $evaluation->getSequence();
        $user = $evaluation->getUser();

        // load the user progression for the sequence (aka the seen/done/etc. flags on steps)
        $stepsProgression = $this->getStepsProgressionForUser($sequence, $user);

        $conditionCheckers = [
            new ProgressionChecker(),
        ];

        // get the success condition of the workspace if any
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

        // the workspace evaluation aggregates the progression/score of all its required/scored resources
        $aggregator = new EvaluationAggregator($conditionCheckers);

        foreach ($sequence->getSteps() as $step) {
            if (!empty($step->getResource()) && $step->isRequired()) {
                // the step contains a required resource, we need to get the evaluation for this resource
                // to compute the step progression
                $resourceEvaluation = $this->resourceEvalManager->getUserEvaluation($step->getResource(), $user, false);
                if (!$resourceEvaluation) {
                    // no evaluation, adds an empty evaluation for correct progression check
                    $resourceEvaluation = new GenericEvaluation(0);
                }

                $aggregator->addEvaluation($resourceEvaluation, $step->isScored());
            } else {
                // no required resource in the step, we only check if the step is seen/done
                $stepDone = !empty($stepsProgression[$step->getUuid()]) && EvaluationStatus::COMPLETED === $stepsProgression[$step->getUuid()];
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

    public function purgeEvaluations(Sequence $sequence): void
    {
        $this->messageBus->dispatch(
            new PurgeSequenceEvaluations($sequence->getId()),
            [new AuthenticationStamp($this->tokenStorage->getToken()?->getUser()->getId())]
        );
    }
}
