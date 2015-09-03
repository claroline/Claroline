<?php

namespace UJM\ExoBundle\Controller\Api;

use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Manager\ExerciseManager;

/**
 * @EXT\Route(requirements={"id"="\d+"}, options={"expose"=true})
 * @EXT\Method("GET")
 */
class ExerciseController
{
    private $manager;

    /**
     * @DI\InjectParams({
     *     "manager" = @DI\Inject("ujm.exo.exercise_manager")
     * })
     *
     * @param ExerciseManager $manager
     */
    public function __construct(ExerciseManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @EXT\Route("/exercises/{id}")
     */
    public function exerciseAction(Exercise $exercise)
    {
        return new JsonResponse($this->manager->exportExercise($exercise));
    }
}
