<?php

namespace UJM\ExoBundle\Tests\Validator\JsonSchema;

use UJM\ExoBundle\Library\Testing\Json\JsonSchemaTestCase;
use UJM\ExoBundle\Validator\JsonSchema\ExerciseValidator;
use UJM\ExoBundle\Validator\JsonSchema\StepValidator;

class ExerciseValidatorTest extends JsonSchemaTestCase
{
    /**
     * @var ExerciseValidator
     */
    private $validator;

    /**
     * @var StepValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stepValidator;

    protected function setUp()
    {
        parent::setUp();

        $this->stepValidator = $this->getMock('UJM\ExoBundle\Validator\JsonSchema\StepValidator', [], [], '', false);
        $this->stepValidator->expects($this->any())
            ->method('validateAfterSchema')
            ->willReturn([]);

        $this->validator = $this->injectJsonSchemaMock(
            new ExerciseValidator($this->stepValidator)
        );
    }

    /**
     * The validator MUST throw errors if the `showCorrectionDate` is set to "date" but no date is specified.
     */
    public function testNoCorrectionDateWhenRequiredThrowsError()
    {
        $exerciseData = $this->loadTestData('exercise/invalid/no-correction-date.json');

        $errors = $this->validator->validate($exerciseData);

        $this->assertGreaterThan(0, count($errors));
        $this->assertTrue(in_array([
            'path' => '/parameters/correctionDate',
            'message' => 'The property `correctionDate` is required when `showCorrectionAt` is "date"',
        ], $errors));
    }

    /**
     * The validator MUST throw errors if random picking of steps is enabled and property `pick` is not set.
     */
    public function testNoPickWhenRequiredThrowsError()
    {
        $exerciseData = $this->loadTestData('exercise/invalid/no-pick.json');

        $errors = $this->validator->validate($exerciseData);

        $this->assertGreaterThan(0, count($errors));
        $this->assertTrue(in_array([
            'path' => '/parameters/randomPick',
            'message' => 'The property `pick` is required when `randomPick` is not "never"',
        ], $errors));
    }

    /**
     * The validator MUST throw error if the user want to pick more steps than there are in the exercise.
     */
    public function testPickGreaterThanStepsThrowsError()
    {
        $exerciseData = $this->loadTestData('exercise/invalid/pick-greater-than-steps.json');

        $errors = $this->validator->validate($exerciseData);

        $this->assertGreaterThan(0, count($errors));
        $this->assertTrue(in_array([
            'path' => '/parameters/pick',
            'message' => 'the property `pick` cannot be greater than the number of steps of the exercise',
        ], $errors));
    }

    /**
     * The validator MUST throw error if the attempt parameters `randomPick` and `randomOrder` are incompatible.
     *
     * We can not use the generated order (randomOrder) in previous papers if we generate new subsets of steps and
     * questions for each paper (randomPick).
     */
    public function testIncompatiblePickAndRandomThrowsError()
    {
        $stepData = $this->loadTestData('exercise/invalid/incompatible-pick-and-random.json');

        $errors = $this->validator->validate($stepData);

        $this->assertGreaterThan(0, count($errors));
        $this->assertTrue(in_array([
            'path' => '/parameters/randomOrder',
            'message' => 'The property `randomOrder` cannot be "once" when `randomPick` is "always"',
        ], $errors));
    }

    /**
     * The validator MUST forward the validation of steps to the StepValidator.
     */
    public function testStepsAreValidatedToo()
    {
        $exerciseData = $this->loadExampleData('quiz/examples/valid/quiz-metadata.json');

        $this->stepValidator->expects($this->exactly(count($exerciseData->steps)))
            ->method('validateAfterSchema');

        $this->validator->validate($exerciseData);
    }
}
