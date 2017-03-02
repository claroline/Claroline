<?php

namespace UJM\ExoBundle\Tests\Entity;

use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Entity\Step;

class ExerciseTest extends \PHPUnit_Framework_TestCase
{
    public function testUuidInitialized()
    {
        $exercise = new Exercise();
        $this->assertFalse(empty($exercise->getUuid()));
    }

    public function testAddStep()
    {
        $exercise = new Exercise();
        $step = new Step();

        $exercise->addStep($step);
        $this->assertCount(1, $exercise->getSteps());
        $this->assertEquals($exercise, $step->getExercise());
        $this->assertEquals(0, $step->getOrder());
    }

    public function testAddStepIncrementOrder()
    {
        // Create new exercise
        $exercise = new Exercise();

        // Add a first step to the exercise
        $step1 = new Step();
        $exercise->addStep($step1);
        $this->assertEquals(0, $step1->getOrder());

        // Add the second one
        $step2 = new Step();
        $exercise->addStep($step2);
        $this->assertEquals(1, $step2->getOrder());
    }

    public function testAddStepNoDuplicate()
    {
        // Initialize some data
        $exercise = new Exercise();
        $step = new Step();

        // Add the same step 2 times
        $exercise->addStep($step);
        $exercise->addStep($step);

        // The exercise MUST contain only 1 step
        $this->assertCount(1, $exercise->getSteps());
    }

    public function testRemoveStep()
    {
        // Initialize some data
        $exercise = new Exercise();
        $step1 = new Step(); // We create one more step to check it is the correct one which is deleted
        $stepToDelete = new Step();

        $exercise->addStep($step1);
        $exercise->addStep($stepToDelete);

        // Remove the step
        $exercise->removeStep($stepToDelete);

        // The exercise MUST not contain the deleted step
        $this->assertCount(1, $exercise->getSteps());
        $this->assertFalse($exercise->getSteps()->contains($stepToDelete));
    }
}
