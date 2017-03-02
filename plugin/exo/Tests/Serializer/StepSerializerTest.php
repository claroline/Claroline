<?php

namespace UJM\ExoBundle\Tests\Serializer;

use UJM\ExoBundle\Entity\Step;
use UJM\ExoBundle\Library\Testing\Json\JsonDataTestCase;
use UJM\ExoBundle\Serializer\StepSerializer;
use UJM\ExoBundle\Validator\JsonSchema\StepValidator;

class StepSerializerTest extends JsonDataTestCase
{
    /**
     * @var StepValidator
     */
    private $validator;

    /**
     * @var StepSerializer
     */
    private $serializer;

    /**
     * @var Step
     */
    private $step;

    protected function setUp()
    {
        parent::setUp();

        // We trust validator service as it is fully tested
        $this->validator = $this->client->getContainer()->get('ujm_exo.validator.step');
        $this->serializer = $this->client->getContainer()->get('ujm_exo.serializer.step');

        $this->step = new Step();
        $this->step->setUuid(uniqid());
        $this->step->setTitle('Step title');
        $this->step->setMaxAttempts(10);
    }

    public function testSerializedDataAreSchemaValid()
    {
        $data = $this->serializer->serialize($this->step);

        $this->assertCount(0, $this->validator->validate($data));
    }

    public function testSerializedDataAreCorrectlySet()
    {
        $data = $this->serializer->serialize($this->step);

        $this->assertInstanceOf('\stdClass', $data);
        $this->assertTrue(!empty($data->id));
        $this->assertTrue(!empty($data->title));
        $this->assertTrue(!empty($data->parameters));
        $this->assertTrue(isset($data->items));

        $this->assertEquals('Step title', $data->title);
        $this->assertEquals(10, $data->parameters->maxAttempts);
    }

    public function testDeserializedDataAreCorrectlySet()
    {
        $stepData = $this->loadTestData('step/valid/empty-items.json');

        $step = $this->serializer->deserialize($stepData);

        $this->assertInstanceOf('\UJM\ExoBundle\Entity\Step', $step);
        $this->assertEquals($stepData->id, $step->getUuid());

        // Checks some parameters
        $this->assertEquals($stepData->parameters->maxAttempts, $step->getMaxAttempts());
    }

    public function testDeserializedDataWithItemsAreCorrectlySet()
    {
        $stepData = $this->loadTestData('step/valid/with-items.json');

        $step = $this->serializer->deserialize($stepData);

        $this->assertTrue(isset($stepData->items));
        $this->assertCount(count($stepData->items), $step->getStepQuestions());
    }

    public function testAddItem()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    public function testRemoveItem()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
}
