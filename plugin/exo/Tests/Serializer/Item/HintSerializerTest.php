<?php

namespace UJM\ExoBundle\Tests\Serializer\Item;

use Claroline\CoreBundle\Persistence\ObjectManager;
use UJM\ExoBundle\Entity\Item\Hint;
use UJM\ExoBundle\Library\Options\Transfer;
use UJM\ExoBundle\Library\Options\Validation;
use UJM\ExoBundle\Library\Testing\Json\JsonDataTestCase;
use UJM\ExoBundle\Serializer\Item\HintSerializer;
use UJM\ExoBundle\Validator\JsonSchema\Item\HintValidator;

class HintSerializerTest extends JsonDataTestCase
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var HintValidator
     */
    private $validator;

    /**
     * @var HintSerializer
     */
    private $serializer;

    private $hint;

    protected function setUp()
    {
        parent::setUp();

        $this->om = $this->client->getContainer()->get('claroline.persistence.object_manager');

        // We trust validator service as it is fully tested
        $this->validator = $this->client->getContainer()->get('ujm_exo.validator.hint');
        $this->serializer = $this->client->getContainer()->get('ujm_exo.serializer.hint');

        $this->hint = new Hint();
        $this->hint->setUuid(uniqid('', true));
        $this->hint->setPenalty(2);
        $this->hint->setData('hint text');

        $this->om->persist($this->hint);
        $this->om->flush();
    }

    /**
     * The serialized data MUST respect the JSON schema.
     */
    public function testSerializedDataAreSchemaValid()
    {
        $serialized = $this->serializer->serialize($this->hint);

        $errors = $this->validator->validate($serialized);

        $this->assertCount(0, $errors);
    }

    public function testSerializedDataWithSolutionsAreSchemaValid()
    {
        $serialized = $this->serializer->serialize($this->hint, [Transfer::INCLUDE_SOLUTIONS]);

        $this->assertCount(0, $this->validator->validate($serialized, [Validation::REQUIRE_SOLUTIONS]));
    }

    public function testSerializedDataAreCorrectlySet()
    {
        $serialized = $this->serializer->serialize($this->hint);

        $this->assertInstanceOf('\stdClass', $serialized);
        $this->assertEquals(2, $serialized->penalty);

        // Checks solutions are not included
        $this->assertTrue(!isset($serialized->value));
    }

    public function testSerializedDataWithNoPenalty()
    {
        $serialized = $this->serializer->serialize(new Hint());

        $this->assertTrue(!isset($serialized->penalty));
    }

    public function testSerializedDataWithSolutions()
    {
        $serialized = $this->serializer->serialize($this->hint, [Transfer::INCLUDE_SOLUTIONS]);

        $this->assertEquals('hint text', $serialized->value);
    }

    public function testDeserializedDataAreCorrectlySet()
    {
        $hintData = $this->loadTestData('hint/valid/full.json');

        $hint = $this->serializer->deserialize($hintData);

        $this->assertInstanceOf('UJM\ExoBundle\Entity\Item\Hint', $hint);
        $this->compareHintAndData($hint, $hintData);
    }

    /**
     * The serializer MUST update the entity object passed as param and MUST NOT create a new one.
     */
    public function testDeserializeUpdateEntityIfExist()
    {
        $hintData = $this->loadTestData('hint/valid/full.json');

        $updatedHint = $this->serializer->deserialize($hintData, $this->hint);

        // The original keyword entity must have been updated
        $this->compareHintAndData($this->hint, $hintData);

        // Checks no new entity have been created
        $nbBefore = count($this->om->getRepository('UJMExoBundle:Item\Hint')->findAll());

        // Save the keyword to DB
        $this->om->persist($updatedHint);
        $this->om->flush();

        $nbAfter = count($this->om->getRepository('UJMExoBundle:Item\Hint')->findAll());

        $this->assertEquals($nbBefore, $nbAfter);
    }

    /**
     * Compares the data between a hint entity and a hint raw object.
     *
     * @param Hint      $hint
     * @param \stdClass $hintData
     */
    private function compareHintAndData(Hint $hint, \stdClass $hintData)
    {
        $this->assertEquals($hintData->penalty, $hint->getPenalty());
        $this->assertEquals($hintData->value, $hint->getData());
    }
}
