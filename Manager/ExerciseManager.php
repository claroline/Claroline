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
    private $paperRepo;
    private $linkHintPaperRepo;
    private $responseRepo;

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
        $this->paperRepo = $this->om->getRepository('UJMExoBundle:Paper');
        $this->linkHintPaperRepo = $this->om->getRepository('UJMExoBundle:LinkHintPaper');
        $this->responseRepo = $this->om->getRepository('UJMExoBundle:Response');
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

        $papers = $this->paperRepo->findByExercise($exercise);

        foreach ($papers as $paper) {
            $links = $this->linkHintPaperRepo->findByPaper($paper);

            foreach ($links as $link) {
                $this->om->remove($link);
            }

            $responses = $this->responseRepo->findByPaper($paper);

            foreach ($responses as $response) {
                $this->om->remove($response);
            }

            $this->om->remove($paper);
        }

        $this->om->flush();
    }
}
