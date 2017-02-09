<?php

namespace UJM\ExoBundle\Tests\Validator\JsonSchema;

use UJM\ExoBundle\Library\Testing\Json\JsonSchemaTestCase;
use UJM\ExoBundle\Validator\JsonSchema\Content\ContentValidator;
use UJM\ExoBundle\Validator\JsonSchema\Item\ItemValidator;
use UJM\ExoBundle\Validator\JsonSchema\StepValidator;

class StepValidatorTest extends JsonSchemaTestCase
{
    /**
     * @var StepValidator
     */
    private $validator;

    /**
     * @var ItemValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $itemValidator;

    /**
     * @var ContentValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contentValidator;

    protected function setUp()
    {
        parent::setUp();

        $this->itemValidator = $this->getMock('UJM\ExoBundle\Validator\JsonSchema\Item\ItemValidator', [], [], '', false);
        $this->itemValidator->expects($this->any())
            ->method('validateAfterSchema')
            ->willReturn([]);

        $this->contentValidator = $this->getMock('UJM\ExoBundle\Validator\JsonSchema\Content\ContentValidator', [], [], '', false);
        $this->contentValidator->expects($this->any())
            ->method('validateAfterSchema')
            ->willReturn([]);

        $this->validator = $this->injectJsonSchemaMock(
            new StepValidator($this->itemValidator, $this->contentValidator)
        );
    }

    /**
     * The validator MUST throw errors if random picking of steps is enabled and property `pick` is not set.
     */
    public function testNoPickWhenRequiredThrowsError()
    {
        $stepData = $this->loadTestData('step/invalid/no-pick.json');

        $errors = $this->validator->validate($stepData);

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
        $stepData = $this->loadTestData('step/invalid/pick-greater-than-items.json');

        $errors = $this->validator->validate($stepData);

        $this->assertGreaterThan(0, count($errors));
        $this->assertTrue(in_array([
            'path' => '/parameters/pick',
            'message' => 'the property `pick` cannot be greater than the number of items of the step',
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
        $stepData = $this->loadTestData('step/invalid/incompatible-pick-and-random.json');

        $errors = $this->validator->validate($stepData);

        $this->assertGreaterThan(0, count($errors));
        $this->assertTrue(in_array([
            'path' => '/parameters/randomOrder',
            'message' => 'The property `randomOrder` cannot be "once" when `randomPick` is "always"',
        ], $errors));
    }

    /**
     * The validator MUST execute custom validation for question items by calling the ItemValidator.
     */
    public function testQuestionsAreValidatedToo()
    {
        $stepData = $this->loadExampleData('step/examples/valid/one-question.json');

        // Checks that question items are forwarded to the ItemValidator
        $this->itemValidator->expects($this->exactly(count($stepData->items)))
            ->method('validateAfterSchema');

        $this->validator->validate($stepData);
    }

    /**
     * The validator MUST execute custom validation for content items by calling the ContentValidator.
     */
    public function testContentsAreValidatedToo()
    {
        $stepData = $this->loadExampleData('step/examples/valid/one-content.json');

        // Checks that content items are forwarded to the ContentValidator
        $this->contentValidator->expects($this->exactly(count($stepData->items)))
            ->method('validateAfterSchema');

        $this->validator->validate($stepData);
    }
}
