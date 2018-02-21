<?php

namespace UJM\ExoBundle\Tests\Validator\JsonSchema\Attempt\AnswerData;

use Claroline\AppBundle\Persistence\ObjectManager;
use UJM\ExoBundle\Entity\Item\Item;
use UJM\ExoBundle\Entity\ItemType\ChoiceQuestion;
use UJM\ExoBundle\Library\Options\Validation;
use UJM\ExoBundle\Library\Testing\Json\JsonSchemaTestCase;
use UJM\ExoBundle\Library\Testing\Persister;
use UJM\ExoBundle\Validator\JsonSchema\Attempt\AnswerData\ChoiceAnswerValidator;

class ChoiceAnswerValidatorTest extends JsonSchemaTestCase
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var ChoiceAnswerValidator
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
        $this->validator = $this->injectJsonSchemaMock(new ChoiceAnswerValidator());

        $persister = new Persister(
            $this->om
        );

        $this->question = $persister->choiceQuestion('Choice question');

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
     * The validator MUST return an error if answer choice id does not reference an existing choice.
     */
    public function testIncoherentChoiceIdThrowsError()
    {
        // Get answer data
        $answerData = $this->loadTestData('answer-data/choice/valid/single-answer.json');

        // Validate answer against the question
        $errors = $this->validator->validate($answerData, [
            Validation::QUESTION => $this->question->getInteraction(),
        ]);

        $this->assertGreaterThan(0, count($errors));
        $this->assertTrue(in_array([
            'path' => '/[0]',
            'message' => 'Answer array identifiers must reference question choices',
        ], $errors));
    }

    /**
     * The validator MUST return an error if multiple answers are submitted to an unique choice question.
     */
    public function testMultipleAnswersToUniqueChoiceQuestionThrowsError()
    {
        /** @var ChoiceQuestion $choiceQuestion */
        $choiceQuestion = $this->question->getInteraction();
        $choiceQuestion->setMultiple(false);

        $this->om->persist($choiceQuestion);
        $this->om->flush();

        // Get answer data
        $answerData = $this->loadTestData('answer-data/choice/valid/multiple-answers.json');

        // Validate answer against the question
        $errors = $this->validator->validate($answerData, [
            Validation::QUESTION => $this->question->getInteraction(),
        ]);

        $this->assertGreaterThan(0, count($errors));
        $this->assertTrue(in_array([
            'path' => '',
            'message' => 'This question does not allow multiple answers',
        ], $errors));
    }
}
