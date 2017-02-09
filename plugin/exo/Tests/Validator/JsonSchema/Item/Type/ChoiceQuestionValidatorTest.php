<?php

namespace UJM\ExoBundle\Tests\Validator\JsonSchema\Item\Type;

use UJM\ExoBundle\Library\Options\Validation;
use UJM\ExoBundle\Library\Testing\Json\JsonSchemaTestCase;
use UJM\ExoBundle\Validator\JsonSchema\Item\Type\ChoiceQuestionValidator;

class ChoiceQuestionValidatorTest extends JsonSchemaTestCase
{
    /**
     * @var ChoiceQuestionValidator
     */
    private $validator;

    protected function setUp()
    {
        parent::setUp();

        $this->validator = $this->injectJsonSchemaMock(
            new ChoiceQuestionValidator()
        );
    }

    /**
     * The validator MUST return errors if there is no solution with a positive score.
     */
    public function testNoSolutionWithPositiveScoreThrowsError()
    {
        $questionData = $this->loadTestData('question/choice/invalid/no-solution-with-positive-score.json');

        $errors = $this->validator->validate($questionData, [Validation::REQUIRE_SOLUTIONS]);

        $this->assertGreaterThan(0, count($errors));
        $this->assertTrue(in_array([
            'path' => '/solutions',
            'message' => 'There is no solution with a positive score',
        ], $errors));
    }

    /**
     * The validator MUST return errors if the solution ids do not match choice ids.
     */
    public function testIncoherentIdsInSolutionThrowErrors()
    {
        $questionData = $this->loadTestData('question/choice/invalid/incoherent-solution-ids.json');

        $errors = $this->validator->validate($questionData, [Validation::REQUIRE_SOLUTIONS]);

        $this->assertGreaterThan(0, count($errors));
        $this->assertTrue(in_array([
            'path' => '/solutions[0]',
            'message' => "id 42 doesn't match any choice id",
        ], $errors));
    }
}
