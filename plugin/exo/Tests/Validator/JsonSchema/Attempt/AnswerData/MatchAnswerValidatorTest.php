<?php

namespace UJM\ExoBundle\Tests\Validator\JsonSchema\Attempt\AnswerData;

use Claroline\AppBundle\Persistence\ObjectManager;
use UJM\ExoBundle\Entity\Item\Item;
use UJM\ExoBundle\Library\Options\Validation;
use UJM\ExoBundle\Library\Testing\Json\JsonSchemaTestCase;
use UJM\ExoBundle\Library\Testing\Persister;
use UJM\ExoBundle\Validator\JsonSchema\Attempt\AnswerData\MatchAnswerValidator;

class MatchAnswerValidatorTest extends JsonSchemaTestCase
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var MatchAnswerValidator
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
        $this->validator = $this->injectJsonSchemaMock(new MatchAnswerValidator());

        $persister = new Persister(
            $this->om
        );

        $this->question = $persister->matchQuestion('Match question');

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
     * The validator MUST return an error if answer `firstId`does not reference an existing first set item.
     */
    public function testIncoherentFirstIdThrowsError()
    {
        // Get answer data
        $answerData = $this->loadTestData('answer-data/match/valid/single-answer.json');

        // Validate answer against the question
        $errors = $this->validator->validate($answerData, [
            Validation::QUESTION => $this->question->getInteraction(),
        ]);

        $this->assertGreaterThan(0, count($errors));
        $this->assertTrue(in_array([
            'path' => '/firstId',
            'message' => 'Answer `firstId` must reference an item from `firstSet`',
        ], $errors));
    }

    /**
     * The validator MUST return an error if answer `secondId`does not reference an existing second set item.
     */
    public function testIncoherentSecondIdThrowsError()
    {
        // Get answer data
        $answerData = $this->loadTestData('answer-data/match/valid/single-answer.json');

        // Validate answer against the question
        $errors = $this->validator->validate($answerData, [
            Validation::QUESTION => $this->question->getInteraction(),
        ]);

        $this->assertGreaterThan(0, count($errors));
        $this->assertTrue(in_array([
            'path' => '/secondId',
            'message' => 'Answer `firstId` must reference an item from `secondSet`',
        ], $errors));
    }
}
