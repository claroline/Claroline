<?php

namespace UJM\ExoBundle\Tests\Validator\JsonSchema\Attempt\AnswerData;

use Claroline\AppBundle\Persistence\ObjectManager;
use UJM\ExoBundle\Entity\Item\Item;
use UJM\ExoBundle\Library\Options\Validation;
use UJM\ExoBundle\Library\Testing\Json\JsonSchemaTestCase;
use UJM\ExoBundle\Library\Testing\Persister;
use UJM\ExoBundle\Validator\JsonSchema\Attempt\AnswerData\SetAnswerValidator;

class SetAnswerValidatorTest extends JsonSchemaTestCase
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var SetAnswerValidator
     */
    private $validator = null;

    /**
     * @var Item
     */
    private $question;

    protected function setUp()
    {
        parent::setUp();

        $this->om = $this->client->getContainer()->get('claroline.persistence.object_manager');
        $this->validator = $this->injectJsonSchemaMock(new SetAnswerValidator());

        $persister = new Persister(
            $this->om
        );

        $this->question = $persister->matchQuestion('Set question');

        $this->om->flush();
    }

    /**
     * The validator MUST throw an exception if the validator is called without a question instance.
     *
     * @expectedException \LogicException
     */
    public function testValidatorThrowsExceptionIfNoQuestionProvided()
    {
        // Validate answer without question
        $this->validator->validate([]);
    }

    /**
     * The validator MUST return an error if answer `itemId`does not reference an existing item.
     */
    public function testIncoherentFirstIdThrowsError()
    {
        // Get answer data
        $answerData = $this->loadTestData('answer-data/set/valid/single-answer.json');

        // Validate answer against the question
        $errors = $this->validator->validate($answerData, [
            Validation::QUESTION => $this->question->getInteraction(),
        ]);

        $this->assertGreaterThan(0, count($errors));
        $this->assertTrue(in_array([
            'path' => '/itemId',
            'message' => 'Answer `itemId` must reference an item from `items`',
        ], $errors));
    }

    /**
     * The validator MUST return an error if answer `setId`does not reference an existing set.
     */
    public function testIncoherentSetIdThrowsError()
    {
        // Get answer data
        $answerData = $this->loadTestData('answer-data/set/valid/single-answer.json');

        // Validate answer against the question
        $errors = $this->validator->validate($answerData, [
            Validation::QUESTION => $this->question->getInteraction(),
        ]);

        $this->assertGreaterThan(0, count($errors));
        $this->assertTrue(in_array([
            'path' => '/setId',
            'message' => 'Answer `setId` must reference an item from `sets`',
        ], $errors));
    }
}
