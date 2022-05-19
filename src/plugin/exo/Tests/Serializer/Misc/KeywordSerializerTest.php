<?php

namespace UJM\ExoBundle\Tests\Serializer\Misc;

use Claroline\AppBundle\Persistence\ObjectManager;
use UJM\ExoBundle\Entity\Misc\Keyword;
use UJM\ExoBundle\Library\Testing\Json\JsonDataTestCase;
use UJM\ExoBundle\Serializer\Misc\KeywordSerializer;
use UJM\ExoBundle\Validator\JsonSchema\Misc\KeywordValidator;

class KeywordSerializerTest extends JsonDataTestCase
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var KeywordValidator
     */
    private $validator;

    /**
     * @var KeywordSerializer
     */
    private $serializer;

    /**
     * @var Keyword
     */
    private $keyword;

    protected function setUp(): void
    {
        parent::setUp();

        $this->om = $this->client->getContainer()->get('Claroline\AppBundle\Persistence\ObjectManager');

        // We trust validator service as it is fully tested
        $this->validator = $this->client->getContainer()->get('UJM\ExoBundle\Validator\JsonSchema\Misc\KeywordValidator');
        $this->serializer = $this->client->getContainer()->get('ujm_exo.serializer.keyword');

        $this->keyword = new Keyword();
        $this->keyword->setText('keyword');
        $this->keyword->setCaseSensitive(true);
        $this->keyword->setScore(5);
        $this->keyword->setFeedback('feedback');

        $this->om->persist($this->keyword);
        $this->om->flush();
    }

    /**
     * The serialized data MUST respect the JSON schema.
     */
    public function testSerializedDataAreSchemaValid()
    {
        $serialized = $this->serializer->serialize($this->keyword);

        $this->assertCount(0, $this->validator->validate($serialized));
    }

    /**
     * The serialized data MUST contain all of the exported properties of a Keyword entity.
     */
    public function testSerializedDataAreCorrectlySet()
    {
        $keywordData = $this->serializer->serialize($this->keyword);

        $this->assertTrue(is_array($keywordData));
        $this->compareKeywordAndData($this->keyword, $keywordData);
    }

    /**
     * The deserialized entity MUST be a Keyword and contain all of the properties of raw data.
     */
    public function testDeserializedDataAreCorrectlySet()
    {
        $keywordData = $this->loadTestData('misc/keyword/valid/full.json');

        $keyword = $this->serializer->deserialize($keywordData);

        $this->assertInstanceOf(Keyword::class, $keyword);
        $this->compareKeywordAndData($keyword, $keywordData);
    }

    /**
     * The serializer MUST update the entity object passed as param and MUST NOT create a new one.
     */
    public function testDeserializeUpdateEntityIfExist()
    {
        $keywordData = $this->loadTestData('misc/keyword/valid/full.json');

        $updatedKeyword = $this->serializer->deserialize($keywordData, $this->keyword);

        // The original keyword entity must have been updated
        $this->compareKeywordAndData($this->keyword, $keywordData);

        // Checks no new entity have been created
        $nbBefore = count($this->om->getRepository(Keyword::class)->findAll());

        // Save the keyword to DB
        $this->om->persist($updatedKeyword);
        $this->om->flush();

        $nbAfter = count($this->om->getRepository(Keyword::class)->findAll());

        $this->assertEquals($nbBefore, $nbAfter);
    }

    /**
     * Compares the data between a keyword entity and a keyword raw object.
     */
    private function compareKeywordAndData(Keyword $keyword, array $keywordData)
    {
        $this->assertEquals($keywordData['text'], $keyword->getText());
        $this->assertEquals($keywordData['caseSensitive'], $keyword->isCaseSensitive());
        $this->assertEquals($keywordData['score'], $keyword->getScore());
        $this->assertEquals($keywordData['feedback'], $keyword->getFeedback());
    }
}
