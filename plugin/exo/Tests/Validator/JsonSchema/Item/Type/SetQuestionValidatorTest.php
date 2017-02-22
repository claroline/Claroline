<?php

namespace UJM\ExoBundle\Tests\Validator\JsonSchema\Item\Type;

use UJM\ExoBundle\Library\Options\Validation;
use UJM\ExoBundle\Library\Testing\Json\JsonSchemaTestCase;
use UJM\ExoBundle\Validator\JsonSchema\Item\Type\SetQuestionValidator;

class SetQuestionValidatorTest extends JsonSchemaTestCase
{
    /**
     * @var SetQuestionValidator
     */
    private $validator;

    protected function setUp()
    {
        parent::setUp();

        $this->validator = $this->injectJsonSchemaMock(
            new SetQuestionValidator()
        );
    }

    /**
     * The validator MUST return errors if there is no solution with a positive score.
     */
    public function testNoSolutionWithPositiveScoreThrowsError()
    {
        $questionData = $this->loadTestData('question/set/invalid/no-solution-with-positive-score.json');

        $errors = $this->validator->validate($questionData, [Validation::REQUIRE_SOLUTIONS]);

        $this->assertGreaterThan(0, count($errors));
        $this->assertTrue(in_array([
            'path' => '/solutions',
            'message' => 'There is no solution with a positive score',
        ], $errors));
    }

    /**
     * The validator MUST return errors if the solution ids do not match member ids.
     */
    public function testIncoherentItemIdsInSolutionThrowErrors()
    {
        $questionData = $this->loadTestData('question/set/invalid/incoherent-solution-item-ids.json');

        $errors = $this->validator->validate($questionData, [Validation::REQUIRE_SOLUTIONS]);

        $this->assertGreaterThan(0, count($errors));
        $this->assertTrue(in_array([
            'path' => '/solutions/associations[0]',
            'message' => "id 42 doesn't match any item id",
        ], $errors));
    }

    /**
     * The validator MUST return errors if the solution ids do not match set ids.
     */
    public function testIncoherentSetIdsInSolutionThrowErrors()
    {
        $questionData = $this->loadTestData('question/set/invalid/incoherent-solution-set-ids.json');

        $errors = $this->validator->validate($questionData, [Validation::REQUIRE_SOLUTIONS]);

        $this->assertGreaterThan(0, count($errors));
        $this->assertTrue(in_array([
            'path' => '/solutions/associations[0]',
            'message' => "id 42 doesn't match any set id",
        ], $errors));
    }

    /**
     * The validator MUST return errors if odd solution ids do not match item ids.
     */
    public function testIncoherentItemIdsInOddThrowErrors()
    {
        $questionData = $this->loadTestData('question/set/invalid/incoherent-odd-item-ids.json');

        $errors = $this->validator->validate($questionData, [Validation::REQUIRE_SOLUTIONS]);

        $this->assertGreaterThan(0, count($errors));
        $this->assertTrue(in_array([
            'path' => '/solutions/odd[0]',
            'message' => "id 123456 doesn't match any item id",
        ], $errors));
    }
}
