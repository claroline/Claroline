<?php

namespace UJM\ExoBundle\Tests\Validator\JsonSchema\Item\Type;

use UJM\ExoBundle\Library\Options\Validation;
use UJM\ExoBundle\Library\Testing\Json\JsonSchemaTestCase;
use UJM\ExoBundle\Validator\JsonSchema\Item\Type\ClozeQuestionValidator;
use UJM\ExoBundle\Validator\JsonSchema\Misc\KeywordValidator;

class ClozeQuestionValidatorTest extends JsonSchemaTestCase
{
    /**
     * @var ClozeQuestionValidator
     */
    private $validator;

    /**
     * @var KeywordValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $keywordValidator;

    protected function setUp()
    {
        parent::setUp();

        // Do not validate Keywords
        $this->keywordValidator = $this->getMock('UJM\ExoBundle\Validator\JsonSchema\Misc\KeywordValidator', [], [], '', false);
        $this->keywordValidator->expects($this->any())
            ->method('validateCollection')
            ->willReturn([]);

        $this->validator = $this->injectJsonSchemaMock(
            new ClozeQuestionValidator($this->keywordValidator)
        );
    }

    /**
     * The validator MUST return an error if there is not the same number of solutions and holes.
     */
    public function testInvalidNumberOfSolutionsThrowsError()
    {
        $questionData = $this->loadTestData('question/cloze/invalid/invalid-number-of-solutions.json');

        $errors = $this->validator->validate($questionData, [Validation::REQUIRE_SOLUTIONS]);

        $this->assertGreaterThan(0, count($errors));
        $this->assertTrue(in_array([
            'path' => '/solutions',
            'message' => 'there must be the same number of solutions and holes',
        ], $errors));
    }

    /**
     * The validator MUST execute validation for its keywords when `solutionRequired`.
     */
    public function testSolutionKeywordsAreValidatedToo()
    {
        $questionData = $this->loadExampleData('question/cloze/examples/valid/multiple-answers.json');

        $this->keywordValidator->expects($this->exactly(count($questionData->solutions)))
            ->method('validateCollection');

        $this->validator->validate($questionData, [Validation::REQUIRE_SOLUTIONS]);
    }
}
