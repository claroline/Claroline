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
    private $om;
    private $validator;
    private $questionManager;

    /**
     * @DI\InjectParams({
     *     "om"         = @DI\Inject("claroline.persistence.object_manager"),
     *     "validator"  = @DI\Inject("ujm.exo.json_validator"),
     *     "manager"    = @DI\Inject("ujm.exo.question_manager")
     * })
     *
     * @param ObjectManager     $om
     * @param Validator         $validator
     * @param QuestionManager   $manager
     */
    public function __construct(ObjectManager $om, Validator $validator, QuestionManager $manager)
    {
        $this->om = $om;
        $this->validator = $validator;
        $this->questionManager = $manager;
    }

    /**
     * Publishes an exercise.
     *
     * @param Exercise $exercise
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
     * @todo optimize request number using repository method(s)
     *
     * Deletes all the papers associated with an exercise.
     *
     * @param Exercise $exercise
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
        $originalQuestions = $questions = $this->om
            ->getRepository('UJMExoBundle:Question')
            ->findByExercise($exercise);
        $questionCount = count($questions);

        if ($exercise->getShuffle() && $questionCount > 1) {
            while ($questions === $originalQuestions) {
                shuffle($questions); // shuffle until we have a new order
            }
        }

        if (($questionToPick = $exercise->getNbQuestion()) > 0) {
            while ($questionToPick > 0) {
                $index = rand(0, count($questions) - 1);
                unset($questions[$index]);
                $questions = array_values($questions); // "re-index" the array
                $questionToPick--;
            }
        }

        return $questions;
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
        $quiz = json_decode($data);

        $errors = $this->validator->validateExercise($quiz);

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

        return [
            'authors' => [$authorName],
            'created' => $node->getCreationDate()->format('Y-m-d H:i:s'),
            'title' => $exercise->getTitle(),
            'description' => $exercise->getDescription(),
            'pick' => $exercise->getNbQuestion(),
            'random' => $exercise->getShuffle(),
            'maxAttempts' => $exercise->getMaxAttempts(),
            'dispButtonInterrupt' => $exercise->getDispButtonInterrupt(),
            'markMode' => $exercise->getMarkMode(),
            'correctionMode' => $exercise->getCorrectionMode(),
        ];
    }

    /**
     * @todo step id
     *
     * @param Exercise  $exercise
     * @param bool      $withSolutions
     * @return array
     */
    private function exportSteps(Exercise $exercise, $withSolutions = true)
    {
        $questionRepo = $this->om->getRepository('UJMExoBundle:Question');

        return array_map(function ($question) use ($withSolutions) {
            return [
                'id' => '(unknown)',
                'items' => [$this->questionManager->exportQuestion($question, $withSolutions)]
            ];
        }, $questionRepo->findByExercise($exercise));
    }
}
