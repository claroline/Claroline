<?php

namespace UJM\ExoBundle\Manager;

use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Entity\Step;
use UJM\ExoBundle\Library\Mode\CorrectionMode;
use UJM\ExoBundle\Transfer\Json\ValidationException;
use UJM\ExoBundle\Transfer\Json\Validator;

/**
 * @DI\Service("ujm.exo.exercise_manager")
 */
class ExerciseManager
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var Validator
     */
    private $validator;

    /**
     * @var StepManager
     */
    private $stepManager;

    /**
     * @DI\InjectParams({
     *     "om"          = @DI\Inject("claroline.persistence.object_manager"),
     *     "validator"   = @DI\Inject("ujm.exo.json_validator"),
     *     "stepManager" = @DI\Inject("ujm.exo.step_manager")
     * })
     *
     * @param ObjectManager $om
     * @param Validator     $validator
     * @param StepManager   $stepManager
     */
    public function __construct(ObjectManager $om, Validator $validator, StepManager $stepManager)
    {
        $this->om = $om;
        $this->validator = $validator;
        $this->stepManager = $stepManager;
    }

    /**
     * Create and add a new Step to an Exercise.
     *
     * @param Exercise $exercise
     *
     * @return Step
     */
    public function addStep(Exercise $exercise)
    {
        $step = new Step();
        $step->setOrder($exercise->getSteps()->count() + 1);

        // Link the Step to the Exercise
        $exercise->addStep($step);

        $this->om->persist($step);
        $this->om->flush();

        return $step;
    }

    /**
     * Delete a Step.
     *
     * @param Exercise $exercise
     * @param Step     $step
     */
    public function deleteStep(Exercise $exercise, Step $step)
    {
        $exercise->removeStep($step);

        // Update steps order
        $steps = $exercise->getSteps();
        foreach ($steps as $pos => $stepToReorder) {
            $stepToReorder->setOrder($pos);

            $this->om->persist($step);
        }

        $this->om->remove($step);
        $this->om->flush();
    }

    /**
     * Reorder the steps of an Exercise.
     *
     * @param Exercise $exercise
     * @param array    $order    an ordered array of Step IDs
     *
     * @return array array of errors if something went wrong
     */
    public function reorderSteps(Exercise $exercise, array $order)
    {
        $steps = $exercise->getSteps();

        /** @var Step $step */
        foreach ($steps as $step) {
            // Get new position of the Step
            $pos = array_search($step->getId(), $order);
            if (-1 === $pos) {
                // We need all the steps, to keep the order coherent
                return [
                    'message' => 'Can not reorder the Exercise. Missing steps in order array.',
                ];
            }

            $step->setOrder($pos);
            $this->om->persist($step);
        }

        $this->om->flush();

        return [];
    }

    /**
     * Publishes an exercise.
     *
     * @param Exercise $exercise
     * @param bool     $throwException Throw an exception if the Exercise is already published
     *
     * @throws \LogicException if the exercise is already published
     */
    public function publish(Exercise $exercise, $throwException = true)
    {
        if ($throwException && $exercise->getResourceNode()->isPublished()) {
            throw new \LogicException("Exercise {$exercise->getId()} is already published");
        }

        if (!$exercise->wasPublishedOnce()) {
            $this->deletePapers($exercise);
            $exercise->setPublishedOnce(true);
        }

        $exercise->getResourceNode()->setPublished(true);
        $this->om->flush();
    }

    /**
     * Unpublishes an exercise.
     *
     * @param Exercise $exercise
     * @param bool     $throwException Throw an exception if the Exercise is not published
     *
     * @throws \LogicException if the exercise is not published
     */
    public function unpublish(Exercise $exercise, $throwException = true)
    {
        if ($throwException && !$exercise->getResourceNode()->isPublished()) {
            throw new \LogicException("Exercise {$exercise->getId()} is already unpublished");
        }

        $exercise->getResourceNode()->setPublished(false);
        $this->om->flush();
    }

    /**
     * Deletes all the papers associated with an exercise.
     *
     * @todo optimize request number using repository method(s)
     *
     * @param Exercise $exercise
     *
     * @throws \Exception if the exercise has been published at least once
     */
    public function deletePapers(Exercise $exercise)
    {
        if ($exercise->wasPublishedOnce()) {
            throw new \Exception(
                "Cannot delete exercise {$exercise->getId()} papers as it has been published at least once"
            );
        }

        $paperRepo = $this->om->getRepository('UJMExoBundle:Paper');
        $linkHintPaperRepo = $this->om->getRepository('UJMExoBundle:LinkHintPaper');
        $responseRepo = $this->om->getRepository('UJMExoBundle:Response');
        $papers = $paperRepo->findByExercise($exercise);

        foreach ($papers as $paper) {
            $links = $linkHintPaperRepo->findByPaper($paper);

            foreach ($links as $link) {
                $this->om->remove($link);
            }

            $responses = $responseRepo->findByPaper($paper);

            foreach ($responses as $response) {
                $this->om->remove($response);
            }

            $this->om->remove($paper);
        }

        $this->om->flush();
    }

    /**
     * Create a copy of an Exercise.
     *
     * @param Exercise $exercise
     *
     * @return Exercise the copy of the Exercise
     */
    public function copyExercise(Exercise $exercise)
    {
        $newExercise = new Exercise();

        // Populate Exercise properties
        $newExercise->setName($exercise->getName());
        $newExercise->setDescription($exercise->getDescription());
        $newExercise->setShuffle($exercise->getShuffle());
        $newExercise->setPickSteps($exercise->getPickSteps());
        $newExercise->setDuration($exercise->getDuration());
        $newExercise->setDoprint($exercise->getDoprint());
        $newExercise->setMaxAttempts($exercise->getMaxAttempts());
        $newExercise->setCorrectionMode($exercise->getCorrectionMode());
        $newExercise->setDateCorrection($exercise->getDateCorrection());
        $newExercise->setMarkMode($exercise->getMarkMode());
        $newExercise->setDispButtonInterrupt($exercise->getDispButtonInterrupt());
        $newExercise->setLockAttempt($exercise->getLockAttempt());

        /** @var \UJM\ExoBundle\Entity\Step $step */
        foreach ($exercise->getSteps() as $step) {
            $newStep = $this->stepManager->copyStep($step);

            // Add step to Exercise
            $newExercise->addStep($newStep);
        }

        return $newExercise;
    }

    /**
     * @todo actual import...
     *
     * Imports an exercise in a JSON format.
     *
     * @param string $data
     *
     * @throws ValidationException if the exercise is not valid
     */
    public function importExercise($data)
    {
        $exerciseData = json_decode($data);

        $errors = $this->validator->validateExercise($exerciseData);

        if (count($errors) > 0) {
            throw new ValidationException('Exercise is not valid', $errors);
        }
    }

    /**
     * Exports an exercise in a JSON-encodable format.
     *
     * @param Exercise $exercise
     * @param bool     $withSolutions
     *
     * @return array
     */
    public function exportExercise(Exercise $exercise, $withSolutions = true)
    {
        if ($exercise->getType() === $exercise::TYPE_FORMATIVE) {
            $withSolutions = true;
        }

        return [
            'id' => $exercise->getId(),
            'meta' => $this->exportMetadata($exercise),
            'steps' => $this->exportSteps($exercise, $withSolutions),
        ];
    }

    /**
     * Exports an exercise in a JSON-encodable format.
     *
     * @param Exercise $exercise
     *
     * @return array
     */
    public function exportExerciseMinimal(Exercise $exercise)
    {
        return [
            'id' => $exercise->getId(),
            'meta' => $this->exportMetadata($exercise),
        ];
    }

    /**
     * Update the Exercise metadata.
     *
     * @param Exercise  $exercise
     * @param \stdClass $metadata
     *
     * @throws ValidationException
     */
    public function updateMetadata(Exercise $exercise, \stdClass $metadata)
    {
        $errors = $this->validator->validateExerciseMetadata($metadata);

        if (count($errors) > 0) {
            throw new ValidationException('Exercise metadata are not valid', $errors);
        }

        // Update ResourceNode
        $node = $exercise->getResourceNode();
        $node->setName($metadata->title);

        // Update Exercise
        $exercise->setDescription($metadata->description);
        $exercise->setType($metadata->type);
        $exercise->setPickSteps($metadata->pick ? $metadata->pick : 0);
        $exercise->setShuffle($metadata->random);
        $exercise->setKeepSteps($metadata->keepSteps);
        $exercise->setMaxAttempts($metadata->maxAttempts);
        $exercise->setLockAttempt($metadata->lockAttempt);
        $exercise->setDispButtonInterrupt($metadata->dispButtonInterrupt);
        $exercise->setMetadataVisible($metadata->metadataVisible);
        $exercise->setMarkMode($metadata->markMode);
        $exercise->setCorrectionMode($metadata->correctionMode);
        $exercise->setAnonymous($metadata->anonymous);
        $exercise->setDuration($metadata->duration);
        $exercise->setStatistics($metadata->statistics ? true : false);

        $correctionDate = null;
        if (!empty($metadata->correctionDate) && CorrectionMode::AFTER_DATE === $metadata->correctionMode) {
            $correctionDate = \DateTime::createFromFormat('Y-m-d\TH:i:s', $metadata->correctionDate);
        }

        $exercise->setDateCorrection($correctionDate);

        // Save to DB
        $this->om->persist($exercise);
        $this->om->flush();
    }

    /**
     * Export metadata of the Exercise in a JSON-encodable format.
     *
     * @param Exercise $exercise
     *
     * @return array
     */
    private function exportMetadata(Exercise $exercise)
    {
        $node = $exercise->getResourceNode();
        $creator = $node->getCreator();
        $authorName = sprintf('%s %s', $creator->getFirstName(), $creator->getLastName());

        // Accessibility dates
        $startDate = $node->getAccessibleFrom()  ? $node->getAccessibleFrom()->format('Y-m-d\TH:i:s')  : null;
        $endDate = $node->getAccessibleUntil() ? $node->getAccessibleUntil()->format('Y-m-d\TH:i:s') : null;
        $correctionDate = $exercise->getDateCorrection() ? $exercise->getDateCorrection()->format('Y-m-d\TH:i:s') : null;

        return [
            'authors' => [
                ['name' => $authorName],
            ],
            'created' => $node->getCreationDate()->format('Y-m-d\TH:i:s'),
            'title' => $node->getName(),
            'description' => $exercise->getDescription(),
            'type' => $exercise->getType(),
            'pick' => $exercise->getPickSteps(),
            'random' => $exercise->getShuffle(),
            'keepSteps' => $exercise->getKeepSteps(),
            'maxAttempts' => $exercise->getMaxAttempts(),
            'lockAttempt' => $exercise->getLockAttempt(),
            'dispButtonInterrupt' => $exercise->getDispButtonInterrupt(),
            'metadataVisible' => $exercise->isMetadataVisible(),
            'statistics' => $exercise->hasStatistics(),
            'anonymous' => $exercise->getAnonymous(),
            'duration' => $exercise->getDuration(),
            'markMode' => $exercise->getMarkMode(),
            'correctionMode' => $exercise->getCorrectionMode(),
            'correctionDate' => $correctionDate,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'published' => $node->isPublished(),
            'publishedOnce' => $exercise->wasPublishedOnce(),
        ];
    }

    /**
     * Export exercise with steps with questions.
     *
     * @param Exercise $exercise
     * @param bool     $withSolutions
     *
     * @return array
     */
    public function exportSteps(Exercise $exercise, $withSolutions = true)
    {
        $steps = $exercise->getSteps();

        $data = [];
        foreach ($steps as $step) {
            $data[] = $this->stepManager->exportStep($step, $withSolutions);
        }

        return $data;
    }
}
