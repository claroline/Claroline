<?php

namespace UJM\ExoBundle\Tests\Validator\JsonSchema\Item\Type;

use UJM\ExoBundle\Library\Options\Validation;
use UJM\ExoBundle\Library\Testing\Json\JsonSchemaTestCase;
use UJM\ExoBundle\Validator\JsonSchema\Item\Type\WordsQuestionValidator;
use UJM\ExoBundle\Validator\JsonSchema\Misc\KeywordValidator;

class WordsQuestionValidatorTest extends JsonSchemaTestCase
{
    /**
     * @var WordsQuestionValidator
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
            new WordsQuestionValidator($this->keywordValidator)
        );
    }

    /**
     * The validator MUST execute validation for its keywords when `solutionRequired`.
     */
    public function testSolutionKeywordsAreValidatedToo()
    {
        $questionData = $this->loadExampleData('question/words/examples/valid/multiple-answers.json');

        $this->keywordValidator->expects($this->exactly(1))
            ->method('validateCollection');

        $this->validator->validate($questionData, [Validation::REQUIRE_SOLUTIONS]);
    }
}
