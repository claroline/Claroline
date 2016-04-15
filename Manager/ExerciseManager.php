<?php

namespace UJM\ExoBundle\Manager;

use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Entity\Exercise;
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
     * Publishes an exercise.
     *
     * @param Exercise $exercise
     *
     * @throws \LogicException if the exercise is already published
     */
    public function publish(Exercise $exercise)
    {
        if ($exercise->getResourceNode()->isPublished()) {
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
     *
     * @throws \LogicException if the exercise is already unpublished
     */
    public function unpublish(Exercise $exercise)
    {
        if (!$exercise->getResourceNode()->isPublished()) {
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
     * Returns a question list according to the *shuffle* and
     * *nbQuestions* parameters of an exercise, i.e. filtered
     * and/or randomized if needed.
     *
     * @param Exercise $exercise
     * @return array
     */
    public function pickQuestions(Exercise $exercise)
    {
        $steps = $this->pickSteps($exercise);
        $finalQuestions = array();

        foreach ($steps as $step) {
            $questions = array();
            $originalQuestions = $questions = $this->om
                ->getRepository('UJMExoBundle:Question')
                ->findByStep($step);
            $questionCount = count($questions);

            if ($exercise->getShuffle() && $questionCount > 1) {
                while ($questions === $originalQuestions) {
                    shuffle($questions); // shuffle until we have a new order
                }
            }
            $finalQuestions = array_merge($finalQuestions, $questions);
        }

        if (($questionToPick = $exercise->getNbQuestion()) > 0) {
            while ($questionToPick > 0) {
                $index = rand(0, count($finalQuestions) - 1);
                unset($finalQuestions[$index]);
                $finalQuestions = array_values($finalQuestions); // "re-index" the array
                $questionToPick--;
            }
        }

        return $finalQuestions;
    }

    /**
     * Returns the step list of an exercise
     *
     * @param Exercise $exercise
     * @return array
     */
    public function pickSteps(Exercise $exercise)
    {
        $steps = $this->om
                      ->getRepository('UJMExoBundle:Step')
                      ->findByExercise($exercise);

        return $steps;
    }

    /**
     * @todo actual import...
     *
     * Imports an exercise in a JSON format.
     *
     * @param string $data
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
     * @param Exercise  $exercise
     * @param bool      $withSolutions
     * @return array
     */
    public function exportExercise(Exercise $exercise, $withSolutions = true)
    {
        return [
            'id' => $exercise->getId(),
            'meta' => $this->exportMetadata($exercise),
            'steps' => $this->exportSteps($exercise, $withSolutions),
        ];
    }

    /**
     * Exports an exercise in a JSON-encodable format.
     *
     * @param Exercise  $exercise
     * @return array
     */
    public function exportExerciseMinimal(Exercise $exercise)
    {
        return [
            'id' => $exercise->getId(),
            'meta' => $this->exportMetadata($exercise)
        ];
    }

    /**
     * Update the Exercise metadata
     * @param Exercise $exercise
     * @param \stdClass $metadata
     * @throws ValidationException
     */
    public function updateMetadata(Exercise $exercise, \stdClass $metadata)
    {
        $errors = $this->validator->validateMetadata($metadata);

        if (count($errors) > 0) {
            throw new ValidationException('Exercise metadata are not valid', $errors);
        }

        $exercise->setTitle($metadata->title);
        $exercise->setDescription($metadata->description);
        $exercise->setType($metadata->type);
        $exercise->setNbQuestion($metadata->pick);
        $exercise->setShuffle($metadata->random);
        $exercise->setKeepSameQuestion($metadata->keepSameQuestions);
        $exercise->setMaxAttempts($metadata->maxAttempts);
        $exercise->setLockAttempt($metadata->lockAttempt);
        $exercise->setDispButtonInterrupt($metadata->dispButtonInterrupt);
        $exercise->setMarkMode($metadata->markMode);
        $exercise->setCorrectionMode($metadata->correctionMode);
        $exercise->setAnonymous($metadata->anonymous);
        $exercise->setDuration($metadata->duration);
        /*$exercise->setDateCorrection($metadata->correctionDate);*/

        // Save to DB
        $this->om->persist($exercise);
        $this->om->flush();
    }

    /**
     * @todo duration
     *
     * @param Exercise $exercise
     * @return array
     */
    private function exportMetadata(Exercise $exercise)
    {
        $node = $exercise->getResourceNode();
        $creator = $node->getCreator();
        $authorName = sprintf('%s %s', $creator->getFirstName(), $creator->getLastName());

        // Accessibility dates
        $startDate = $node->getAccessibleFrom()  ? $node->getAccessibleFrom()->format('Y-m-d H:i:s')  : null;
        $endDate   = $node->getAccessibleUntil() ? $node->getAccessibleUntil()->format('Y-m-d H:i:s') : null;

        return [
            'authors' => [
                [ 'name' => $authorName ],
            ],
            'created' => $node->getCreationDate()->format('Y-m-d H:i:s'),
            'title' => $exercise->getTitle(),
            'description' => $exercise->getDescription(),
            'type' => $exercise->getType(),
            'pick' => $exercise->getNbQuestion(),
            'random' => $exercise->getShuffle(),
            'keepSameQuestions' => $exercise->getKeepSameQuestion(),
            'maxAttempts' => $exercise->getMaxAttempts(),
            'lockAttempt' => $exercise->getLockAttempt(),
            'dispButtonInterrupt' => $exercise->getDispButtonInterrupt(),
            'anonymous' => $exercise->getAnonymous(),
            'duration' => $exercise->getDuration(),
            'markMode' => $exercise->getMarkMode(),
            'correctionMode' => $exercise->getCorrectionMode(),
            'correctionDate' => $exercise->getDateCorrection()->format('Y-m-d H:i:s'),
            'startDate' => $startDate,
            'endDate' => $endDate,
            'published' => $node->isPublished(),
            'publishedOnce' => $exercise->wasPublishedOnce()
        ];
    }

    /**
     * Export exercise with steps with questions
     *
     * @param Exercise  $exercise
     * @param bool      $withSolutions
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
