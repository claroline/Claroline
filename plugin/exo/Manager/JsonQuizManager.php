<?php

namespace UJM\ExoBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Manager\ResourceManager;
use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Library\Options\Transfer;
use UJM\ExoBundle\Serializer\ExerciseSerializer;

/**
 * @DI\Service("ujm_exo.manager.json_quiz")
 */
class JsonQuizManager
{
    /**
     * @var ObjectManager
     */
    private $om;

    private $serializer;

    private $resourceManager;

    private $exerciseManager;

    /**
     * HintManager constructor.
     *
     * @DI\InjectParams({
     *     "objectManager" = @DI\Inject("claroline.persistence.object_manager"),
     *     "serializer"    = @DI\Inject("ujm_exo.serializer.exercise"),
     *     "resourceManager" = @DI\Inject("claroline.manager.resource_manager"),
     *     "exerciseManager" = @DI\Inject("ujm_exo.manager.exercise")
     * })
     *
     * @param ObjectManager $objectManager
     */
    public function __construct(
        ObjectManager $objectManager,
        ExerciseSerializer $serializer,
        ResourceManager $resourceManager,
        ExerciseManager $exerciseManager
    ) {
        $this->om = $objectManager;
        $this->serializer = $serializer;
        $this->resourceManager = $resourceManager;
        $this->exerciseManager = $exerciseManager;
    }

    public function export(Exercise $exercise)
    {
        $data = $this->serializer->serialize(
            $exercise,
            [Transfer::INCLUDE_SOLUTIONS, Transfer::INCLUDE_ADMIN_META]
        );
        $filename = tempnam($exercise->getResourceNode()->getName(), '');
        file_put_contents($filename, json_encode($data), FILE_APPEND);

        return $filename;
    }

    public function import(\stdClass $data, $workspace, $owner)
    {
        $exercise = new Exercise();
        $exercise->setName($data->title);
        // Create entities from import data
        $exercise = $this->exerciseManager->createCopy($data, $exercise);
        $parent = $this->resourceManager->getWorkspaceRoot($workspace);

        $node = $this->resourceManager->create(
          $exercise,
          $this->resourceManager->getResourceTypeByName('ujm_exercise'),
          $owner,
          $workspace,
          $parent
        );

        return $node;
    }
}
