<?php

namespace Innova\PathBundle\Manager;

use Claroline\CoreBundle\Entity\Resource\ResourceEvaluation;
use Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Repository\Resource\ResourceEvaluationRepository;
use Claroline\EvaluationBundle\Library\Checker\ProgressionChecker;
use Claroline\EvaluationBundle\Library\Checker\ScoreChecker;
use Claroline\EvaluationBundle\Library\EvaluationAggregator;
use Claroline\EvaluationBundle\Library\EvaluationStatusChecker;
use Claroline\EvaluationBundle\Library\GenericEvaluation;
use Claroline\EvaluationBundle\Manager\ResourceEvaluationManager;
use Doctrine\Persistence\ObjectManager;
use Innova\PathBundle\Entity\Path\Path;
use Innova\PathBundle\Entity\Step;
use Innova\PathBundle\Entity\UserProgression;
use Innova\PathBundle\Repository\PathRepository;

class EvaluationManager
{
    /** @var ObjectManager */
    private $om;
    /** @var ResourceEvaluationManager */
    private $resourceEvalManager;

    private $progressionRepo;
    /** @var PathRepository */
    private $pathRepo;
    /** @var ResourceEvaluationRepository */
    private $resourceEvalRepo;

    public function __construct(
        ObjectManager $om,
        ResourceEvaluationManager $resourceEvalManager
    ) {
        $this->om = $om;
        $this->resourceEvalManager = $resourceEvalManager;

        $this->progressionRepo = $this->om->getRepository(UserProgression::class);
        $this->pathRepo = $this->om->getRepository(Path::class);
        $this->resourceEvalRepo = $this->om->getRepository(ResourceEvaluation::class);
    }

    /**
     * Fetch or create resource user evaluation.
     */
    public function getResourceUserEvaluation(Path $path, User $user): ResourceUserEvaluation
    {
        return $this->resourceEvalManager->getUserEvaluation($path->getResourceNode(), $user);
    }

    public function getCurrentAttempt(Path $path, User $user, ?bool $createMissing = true): ?ResourceEvaluation
    {
        $pathAttempt = $this->resourceEvalRepo->findLast($path->getResourceNode(), $user);
        if (empty($pathAttempt) && $createMissing) {
            $pathAttempt = $this->resourceEvalManager->createAttempt($path->getResourceNode(), $user);
        }

        return $pathAttempt;
    }

    public function getRequiredEvaluations(Path $path, User $user)
    {
        return $this->pathRepo->findRequiredEvaluations($path, $user);
    }

    /**
     * Get all steps progression for an user.
     */
    public function getStepsProgressionForUser(Path $path, User $user): array
    {
        $stepsProgression = [];

        foreach ($path->getSteps() as $step) {
            $userProgression = $this->progressionRepo->findOneBy(['step' => $step, 'user' => $user]);

            if ($userProgression) {
                $stepsProgression[$step->getUuid()] = $userProgression->getStatus();
            }
        }

        return $stepsProgression;
    }

    public function update(Step $step, User $user, string $status): UserProgression
    {
        // Retrieve the current progression for this step
        $progression = $this->progressionRepo->findOneBy([
            'step' => $step,
            'user' => $user,
        ]);

        if (empty($progression)) {
            // No progression for User => initialize a new one
            $progression = new UserProgression();
            $progression->setStep($step);
            $progression->setUser($user);
        }

        if ('seen' !== $status || 'unseen' === $progression->getStatus()) {
            $progression->setStatus($status);
        }

        $this->om->persist($progression);
        $this->om->flush();

        // recompute path progression for user
        $this->compute($step->getPath(), $user);

        return $progression;
    }

    public function compute(Path $path, User $user): ResourceEvaluation
    {
        // get the current attempt of the path
        // for now path can only have one attempt, that's why we retrieve the last attempt without checking if it's already ended.
        $pathAttempt = $this->resourceEvalRepo->findLast($path->getResourceNode(), $user);

        $evaluationData = $pathAttempt && $pathAttempt->getData() ? $pathAttempt->getData() : ['done' => []];

        // load the user progression for the path (aka the seen/done/etc. flags on steps)
        $stepsProgression = $this->getStepsProgressionForUser($path, $user);

        // the path evaluation aggregates the progression/score of all its required/evaluated resource
        // and the status of the steps which don't contain any resource.
        $aggregator = new EvaluationAggregator();

        if (!empty($path->getOverviewResource()) && $path->getOverviewResource()->isRequired()) {
            $resourceEvaluation = $this->resourceEvalManager->getUserEvaluation($path->getOverviewResource(), $user, false);
            if (!$resourceEvaluation) {
                // no evaluation, adds an empty evaluation for correct progression check
                $resourceEvaluation = new GenericEvaluation(0);
            }

            $aggregator->addEvaluation($resourceEvaluation, $path->getOverviewResource()->isEvaluated());
        }

        foreach ($path->getSteps() as $step) {
            if (!empty($step->getResource()) && $step->getResource()->isRequired()) {
                // the step contains a required resource, we need to get the evaluation for this resource
                // in order to compute the step progression
                $resourceEvaluation = $this->resourceEvalManager->getUserEvaluation($step->getResource(), $user, false);
                if (!$resourceEvaluation) {
                    // no evaluation, adds an empty evaluation for correct progression check
                    $resourceEvaluation = new GenericEvaluation(0);
                }

                $aggregator->addEvaluation($resourceEvaluation, $step->getResource()->isEvaluated());
            } else {
                // no required resource in the step, we only check if the step is seen/done
                $stepDone = !empty($stepsProgression[$step->getUuid()]) && in_array($stepsProgression[$step->getUuid()], ['seen', 'done']);
                $aggregator->addEvaluation(new GenericEvaluation($stepDone ? 100 : 0));

                // store the step status in the path attempt
                if ($stepDone && (empty($evaluationData['done']) || !in_array($step->getUuid(), $evaluationData['done']))) {
                    // mark the step as done if it has the correct status
                    $evaluationData['done'][] = $step->getUuid();
                } elseif (!$stepDone && !empty($evaluationData['done']) && in_array($step->getUuid(), $evaluationData['done'])) {
                    // mark the step as not done
                    array_splice($evaluationData['done'], array_search($step->getUuid(), $evaluationData['done']), 1);
                }
            }
        }

        // compute the status of the path
        $checker = new EvaluationStatusChecker([
            new ProgressionChecker(),
            new ScoreChecker($path->getSuccessScore()),
        ]);

        $evaluationData = [
            'status' => $checker->getStatus($aggregator),
            'score' => $aggregator->getScore(),
            'scoreMax' => $aggregator->getScoreMax(),
            'progression' => $aggregator->getProgression(),
            'data' => $evaluationData,
        ];

        if ($pathAttempt) {
            return $this->resourceEvalManager->updateAttempt($pathAttempt, $evaluationData);
        }

        return $this->resourceEvalManager->createAttempt(
            $path->getResourceNode(),
            $user,
            $evaluationData
        );
    }
}
