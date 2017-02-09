<?php

namespace UJM\ExoBundle\Tests\Serializer\Item;

use UJM\ExoBundle\Library\Testing\Json\JsonDataTestCase;
use UJM\ExoBundle\Serializer\Item\ItemSerializer;
use UJM\ExoBundle\Validator\JsonSchema\Item\ItemValidator;

class ItemSerializerTest extends JsonDataTestCase
{
    /**
     * @var ItemValidator
     */
    private $validator;

    /**
     * @var ItemSerializer
     */
    private $serializer;

    protected function setUp()
    {
        parent::setUp();

        // We trust validator service as it is fully tested
        $this->validator = $this->client->getContainer()->get('ujm_exo.validator.item');
        $this->serializer = $this->client->getContainer()->get('ujm_exo.serializer.item');
    }

    public function testSerializedDataAreSchemaValid()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    public function testSerializedDataAreCorrectlySet()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    public function testDeserializedDataAreCorrectlySet()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    public function testSerializeAdminMetaOption()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    public function testSerializeMinimalOption()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    public function testSetCreatorOnCreate()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    public function testAddCategory()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    public function testAddDefaultCategoryIfNoOne()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    public function testChangeCategory()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    public function testAddHint()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    public function testRemoveHint()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    public function testAddQuestionObject()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    public function testRemoveQuestionObject()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    public function testAddQuestionResource()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    public function testRemoveQuestionResource()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
}
