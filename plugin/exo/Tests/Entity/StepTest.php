<?php

namespace UJM\ExoBundle\Tests\Entity;

use UJM\ExoBundle\Entity\Item\Item;
use UJM\ExoBundle\Entity\Step;
use UJM\ExoBundle\Entity\StepItem;

class StepTest extends \PHPUnit_Framework_TestCase
{
    public function testUuidInitialized()
    {
        $step = new Step();
        $this->assertFalse(empty($step->getUuid()));
    }

    /**
     * The `getQuestions` method is just a shortcut to access the questions without passing by StepItem entities
     * so it MUST return the same number of elements than `getStepQuestions`.
     */
    public function testGetQuestions()
    {
        $step = new Step();

        // Adds a new question in the step
        $stepQuestion = new StepItem();
        $stepQuestion->setQuestion(new Item());
        $step->addStepQuestion($stepQuestion);

        $this->assertCount($step->getStepQuestions()->count(), $step->getQuestions());
    }

    public function testAddQuestion()
    {
        $step = new Step();
        $step->addQuestion(new Item());
        $step->addQuestion(new Item());

        $this->assertCount(2, $step->getQuestions());
        $this->assertCount(2, $step->getStepQuestions());
    }

    public function testAddQuestionIncrementOrder()
    {
        $step = new Step();

        // Add a first question
        $question1 = new Item();
        $step->addQuestion($question1);
        $stepQuestion1 = $this->getStepQuestion($step, $question1);
        $this->assertEquals(0, $stepQuestion1->getOrder());

        // Add the second one which MUST have an order of 1 (the first as a 0 order)
        $question2 = new Item();
        $step->addQuestion($question2);
        $stepQuestion2 = $this->getStepQuestion($step, $question2);
        $this->assertEquals(1, $stepQuestion2->getOrder());
    }

    public function testAddQuestionNoDuplicate()
    {
        $step = new Step();
        $question = new Item();

        // Add the same question 2 times
        $step->addQuestion($question);
        $step->addQuestion($question);

        // The exercise MUST contain only 1 question
        $this->assertCount(1, $step->getQuestions());
    }

    public function testRemoveQuestion()
    {
        // Initialize some data
        $step = new Step();
        $question1 = new Item(); // We create one more question to check it is the correct one which is deleted
        $questionToDelete = new Item();

        $step->addQuestion($question1);
        $step->addQuestion($questionToDelete);

        // Remove the step
        $stepQuestionToDelete = $this->getStepQuestion($step, $questionToDelete);
        $step->removeStepQuestion($stepQuestionToDelete);

        // The exercise MUST not contain the deleted step
        $this->assertCount(1, $step->getQuestions());
        $this->assertCount(1, $step->getStepQuestions());
        $this->assertFalse($step->getStepQuestions()->contains($stepQuestionToDelete));
        $this->assertFalse(in_array($questionToDelete, $step->getQuestions()));
    }

    private function getStepQuestion(Step $step, Item $question)
    {
        $found = null;

        $stepQuestions = $step->getStepQuestions();
        foreach ($stepQuestions as $stepQuestion) {
            if ($question === $stepQuestion->getQuestion()) {
                $found = $stepQuestion;
                break;
            }
        }

        return $found;
    }
}
