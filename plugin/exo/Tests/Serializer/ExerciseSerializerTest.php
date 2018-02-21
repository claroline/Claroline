<?php

namespace UJM\ExoBundle\Tests\Serializer;

use Claroline\AppBundle\Persistence\ObjectManager;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Library\Options\Transfer;
use UJM\ExoBundle\Library\Testing\Json\JsonDataTestCase;
use UJM\ExoBundle\Library\Testing\Persister;
use UJM\ExoBundle\Serializer\ExerciseSerializer;
use UJM\ExoBundle\Validator\JsonSchema\ExerciseValidator;

class ExerciseSerializerTest extends JsonDataTestCase
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var Persister
     */
    private $persister;

    /**
     * @var ExerciseValidator
     */
    private $validator;

    /**
     * @var ExerciseSerializer
     */
    private $serializer;

    /**
     * @var Exercise
     */
    private $exercise;

    protected function setUp()
    {
        parent::setUp();

        $this->om = $this->client->getContainer()->get('claroline.persistence.object_manager');
        $this->persister = new Persister($this->om);

        // We trust validator service as it is fully tested
        $this->validator = $this->client->getContainer()->get('ujm_exo.validator.exercise');
        $this->serializer = $this->client->getContainer()->get('ujm_exo.serializer.exercise');

        // Create an instance of Exercise for tests
        $this->exercise = $this->persister->exercise(
            'Title of my exercise',
            [
                $this->persister->openQuestion('question 1'),
                $this->persister->openQuestion('question 2'),
            ],
            $this->persister->user('john')
        );

        $this->om->persist($this->exercise);
        $this->om->flush();
    }

    /**
     * The serialized data MUST respect the JSON schema.
     */
    public function testSerializedDataAreSchemaValid()
    {
        $data = $this->serializer->serialize($this->exercise);
        $this->assertCount(0, $this->validator->validate($data));
    }

    /**
     * The serialized data MUST contain all of the exported properties of an Exercise entity.
     */
    public function testSerializedDataAreCorrectlySet()
    {
        $data = $this->serializer->serialize($this->exercise);

        $this->assertInstanceOf('\stdClass', $data);
        $this->assertTrue(!empty($data->id));
        $this->assertTrue(!empty($data->title));
        $this->assertTrue(!empty($data->description));
        $this->assertTrue(!empty($data->meta));
        $this->assertTrue(!empty($data->parameters));
        $this->assertTrue(!empty($data->steps));

        // Checks parameters that need transformation
        $this->assertEquals('never', $data->picking->randomOrder);
        $this->assertEquals('never', $data->picking->randomPick);
        $this->assertEquals(0, $data->picking->pick);
    }

    /**
     * The serializer MUST return a minimal representation of the Exercise if the option `minimal` is set.
     * In this case `description`, `parameters`, `steps` MUST be excluded.
     */
    public function testSerializeMinimalOption()
    {
        $data = $this->serializer->serialize($this->exercise, [Transfer::MINIMAL]);

        $this->assertFalse(property_exists($data, 'steps'));
        $this->assertFalse(property_exists($data, 'parameters'));
        $this->assertFalse(property_exists($data, 'description'));
    }

    /**
     * The deserialized entity MUST be an Exercise and contain all of the properties of raw data.
     */
    public function testDeserializedDataAreCorrectlySet()
    {
        $exerciseData = $this->loadTestData('exercise/valid/with-steps.json');

        $exercise = $this->serializer->deserialize($exerciseData);

        $this->assertInstanceOf('\UJM\ExoBundle\Entity\Exercise', $exercise);
        $this->assertEquals($exerciseData->id, $exercise->getUuid());

        // Checks some parameters
        $this->assertEquals($exerciseData->picking->randomOrder, $exercise->getRandomOrder());
        $this->assertEquals($exerciseData->picking->randomPick, $exercise->getRandomPick());
        $this->assertEquals($exerciseData->picking->pick, $exercise->getPick());

        // Checks there is the correct number of steps
        $this->assertCount(count($exerciseData->steps), $exercise->getSteps());
    }

    /**
     * The serializer MUST update the entity object passed as param and MUST NOT create a new one.
     */
    public function testDeserializeUpdateEntityIfExist()
    {
        $exerciseData = $this->loadTestData('exercise/valid/with-steps.json');

        $updatedExercise = $this->serializer->deserialize($exerciseData, $this->exercise);

        // Checks some parameters
        $this->assertEquals($exerciseData->picking->randomOrder, $updatedExercise->getRandomOrder());
        $this->assertEquals($exerciseData->picking->randomPick, $updatedExercise->getRandomPick());
        $this->assertEquals($exerciseData->picking->pick, $updatedExercise->getPick());

        // Checks there is the correct number of steps
        $this->assertCount(count($exerciseData->steps), $this->exercise->getSteps());

        // Checks no new entity have been created
        $nbBefore = count($this->om->getRepository('UJMExoBundle:Exercise')->findAll());

        $this->om->persist($updatedExercise);
        $this->om->flush();

        $nbAfter = count($this->om->getRepository('UJMExoBundle:Exercise')->findAll());

        $this->assertEquals($nbBefore, $nbAfter);
    }

    public function testAddStep()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    public function testRemoveStep()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
}
