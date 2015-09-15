<?php

namespace UJM\ExoBundle\Manager;

use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Entity\Exercise;

/**
 * @DI\Service("ujm.exo.exercise_manager")
 */
class ExerciseManager
{
    private $om;

    /**
     * @DI\InjectParams({
     *     "om" = @DI\Inject("claroline.persistence.object_manager")
     * })
     *
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
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
     * Deletes all the papers associated with an exercise.
     *
     * @todo optimize request number using repository method(s)
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
}
