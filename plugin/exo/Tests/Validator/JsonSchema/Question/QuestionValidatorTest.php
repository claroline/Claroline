<?php

namespace UJM\ExoBundle\Tests\Validator\JsonSchema\Question;

use UJM\ExoBundle\Library\Options\Validation;
use UJM\ExoBundle\Library\Question\QuestionDefinitionsCollection;
use UJM\ExoBundle\Library\Testing\Json\JsonSchemaTestCase;
use UJM\ExoBundle\Validator\JsonSchema\Question\CategoryValidator;
use UJM\ExoBundle\Validator\JsonSchema\Question\HintValidator;
use UJM\ExoBundle\Validator\JsonSchema\Question\QuestionValidator;

class QuestionValidatorTest extends JsonSchemaTestCase
{
    /**
     * @var QuestionValidator
     */
    private $validator;

    /**
     * @var QuestionDefinitionsCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $questionDefinitions;

    /**
     * @var CategoryValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $categoryValidator;

    /**
     * @var HintValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $hintValidator;

    protected function setUp()
    {
        parent::setUp();

        // Mock Question Type validation (it's tested individually)
        $this->questionDefinitions = $this->getMock('UJM\ExoBundle\Library\Question\QuestionDefinitionsCollection', [], [], '', false);

        // Do not check if the Question Type is supported
        $this->questionDefinitions
            ->expects($this->any())
            ->method('has')
            ->willReturn(true);

        // Do not validate Question Type specific data
        $this->questionDefinitions
            ->expects($this->any())
            ->method('validateQuestion')
            ->willReturn([]);

        // Do not validate Categories
        $this->categoryValidator = $this->getMock('UJM\ExoBundle\Validator\JsonSchema\Question\CategoryValidator', [], [], '', false);
        $this->categoryValidator->expects($this->any())
            ->method('validateAfterSchema')
            ->willReturn([]);

        // Do not validate Hints
        $this->hintValidator = $this->getMock('UJM\ExoBundle\Validator\JsonSchema\Question\HintValidator', [], [], '', false);
        $this->hintValidator->expects($this->any())
            ->method('validateAfterSchema')
            ->willReturn([]);

        $this->validator = $this->injectJsonSchemaMock(
            new QuestionValidator($this->questionDefinitions, $this->categoryValidator, $this->hintValidator)
        );
    }

    /**
     * The validator MUST return an error if question has an invalid type.
     */
    public function testUnknownQuestionTypeThrowsError()
    {
        // We don't use `$this->validator` as we have mocked this part for other tests
        $validator = $this->client->getContainer()->get('ujm_exo.validator.question');

        $questionData = $this->loadTestData('question/base/invalid/unknown-type.json');

        $errors = $validator->validate($questionData);

        $this->assertGreaterThan(0, count($errors));
        $this->assertTrue(in_array([
            'path' => '/type',
            'message' => 'Unknown question type "'.$questionData->type.'"',
        ], $errors));
    }

    /**
     * The validator MUST return an error if question has empty content.
     */
    public function testEmptyContentThrowsError()
    {
        $questionData = $this->loadTestData('question/base/invalid/empty-content.json');

        $errors = $this->validator->validate($questionData);

        $this->assertGreaterThan(0, count($errors));
        $this->assertTrue(in_array([
            'path' => '/content',
            'message' => 'Question content can not be empty',
        ], $errors));
    }

    /**
     * The validator MUST return an error if question has no score.
     */
    public function testMissingScoreThrowsError()
    {
        $questionData = $this->loadTestData('question/base/invalid/no-score.json');

        $errors = $this->validator->validate($questionData);

        $this->assertGreaterThan(0, count($errors));
        $this->assertTrue(in_array([
            'path' => '/score',
            'message' => 'Question score is required',
        ], $errors));
    }

    /**
     * The validator MUST return an error if question has no solution and the `solutionsRequired` option is set to true.
     */
    public function testMissingSolutionsWhenRequiredThrowsError()
    {
        $questionData = $this->loadTestData('question/base/invalid/unknown-type.json');

        $errors = $this->validator->validate($questionData, [Validation::REQUIRE_SOLUTIONS]);

        $this->assertGreaterThan(0, count($errors));
        $this->assertTrue(in_array([
            'path' => '/solutions',
            'message' => 'Question requires a "solutions" property',
        ], $errors));
    }

    public function testCategoryIsValidatedToo()
    {
        $questionData = $this->loadExampleData('question/base/examples/valid/with-metadata.json');

        $this->categoryValidator->expects($this->once())
            ->method('validateAfterSchema');

        $this->validator->validate($questionData);
    }

    /**
     * The validator MUST execute custom validation for the hints.
     */
    public function testHintsAreValidatedToo()
    {
        $questionData = $this->loadExampleData('question/base/examples/valid/with-hints.json');

        $this->hintValidator->expects($this->exactly(count($questionData->hints)))
            ->method('validateAfterSchema');

        $this->validator->validate($questionData);
    }
}
