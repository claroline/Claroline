<?php

namespace UJM\ExoBundle\Manager;

use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Library\Options\Transfer;
use UJM\ExoBundle\Library\Options\Validation;
use UJM\ExoBundle\Library\Validator\ValidationException;
use UJM\ExoBundle\Manager\Attempt\PaperManager;
use UJM\ExoBundle\Repository\ExerciseRepository;
use UJM\ExoBundle\Serializer\ExerciseSerializer;
use UJM\ExoBundle\Validator\JsonSchema\ExerciseValidator;

/**
 * @DI\Service("ujm_exo.manager.exercise")
 */
class ExerciseManager
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var ExerciseRepository
     */
    private $repository;

    /**
     * @var ExerciseValidator
     */
    private $validator;

    /**
     * @var ExerciseSerializer
     */
    private $serializer;

    /**
     * @var PaperManager
     */
    private $paperManager;

    /**
     * ExerciseManager constructor.
     *
     * @DI\InjectParams({
     *     "om"           = @DI\Inject("claroline.persistence.object_manager"),
     *     "validator"    = @DI\Inject("ujm_exo.validator.exercise"),
     *     "serializer"   = @DI\Inject("ujm_exo.serializer.exercise"),
     *     "paperManager" = @DI\Inject("ujm_exo.manager.paper")
     * })
     *
     * @param ObjectManager      $om
     * @param ExerciseValidator  $validator
     * @param ExerciseSerializer $serializer
     * @param PaperManager       $paperManager
     */
    public function __construct(
        ObjectManager $om,
        ExerciseValidator $validator,
        ExerciseSerializer $serializer,
        PaperManager $paperManager)
    {
        $this->om = $om;
        $this->repository = $this->om->getRepository('UJMExoBundle:Exercise');
        $this->validator = $validator;
        $this->serializer = $serializer;
        $this->paperManager = $paperManager;
    }

    /**
     * Validates and creates a new Exercise from raw data.
     *
     * @param \stdClass $data
     *
     * @return Exercise
     *
     * @throws ValidationException
     */
    public function create(\stdClass $data)
    {
        return $this->update(new Exercise(), $data);
    }

    /**
     * Validates and updates an Exercise entity with raw data.
     *
     * @param Exercise  $exercise
     * @param \stdClass $data
     *
     * @return Exercise
     *
     * @throws ValidationException
     */
    public function update(Exercise $exercise, \stdClass $data)
    {
        // Validate received data
        $errors = $this->validator->validate($data, [Validation::REQUIRE_SOLUTIONS]);
        if (count($errors) > 0) {
            throw new ValidationException('Exercise is not valid', $errors);
        }

        // Update Exercise with new data
        $this->serializer->deserialize($data, $exercise);

        // Save to DB
        $this->om->persist($exercise);
        $this->om->flush();

        // Invalidate unfinished papers
        $this->repository->invalidatePapers($exercise);

        return $exercise;
    }

    /**
     * Exports an Exercise.
     *
     * @param Exercise $exercise
     * @param array    $options
     *
     * @return \stdClass
     */
    public function export(Exercise $exercise, array $options = [])
    {
        return $this->serializer->serialize($exercise, $options);
    }

    /**
     * Creates a copy of an Exercise.
     *
     * @param Exercise $exercise
     *
     * @return Exercise
     */
    public function copy(Exercise $exercise)
    {
        // Serialize quiz entities
        $exerciseData = $this->serializer->serialize($exercise, [Transfer::INCLUDE_SOLUTIONS]);

        // NB 1. We don't validate data because it comes from the DB and it's always valid
        // Populate new entities with original data
        // NB 2. We use server generated ids for entity creation because the client ones are already in the DB
        // and we need some fresh ones to avoid duplicates
        $newExercise = $this->serializer->deserialize($exerciseData, null, [Transfer::USE_SERVER_IDS]);

        // Save copy to db
        $this->om->persist($newExercise);
        $this->om->flush();

        return $newExercise;
    }

    /**
     * Checks if an Exercise can be deleted.
     * The exercise needs to be unpublished or have no paper to be safely removed.
     *
     * @param Exercise $exercise
     *
     * @return bool
     */
    public function isDeletable(Exercise $exercise)
    {
        return !$exercise->getResourceNode()->isPublished()
            || 0 === $this->paperManager->countExercisePapers($exercise);
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
        if (!$exercise->wasPublishedOnce()) {
            $this->paperManager->deleteAll($exercise);
            $exercise->setPublishedOnce(true);
        }

        $exercise->getResourceNode()->setPublished(true);
        $this->om->flush();
    }

    /**
     * Unpublishes an exercise.
     *
     * @param Exercise $exercise
     */
    public function unpublish(Exercise $exercise)
    {
        $exercise->getResourceNode()->setPublished(false);
        $this->om->flush();
    }
}
