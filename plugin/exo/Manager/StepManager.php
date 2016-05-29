<?php

namespace UJM\ExoBundle\Manager;

use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Entity\Question;
use UJM\ExoBundle\Entity\Step;
use UJM\ExoBundle\Entity\StepQuestion;

/**
 * @DI\Service("ujm.exo.step_manager")
 */
class StepManager
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var QuestionManager
     */
    private $questionManager;

    /**
     * StepManager constructor.
     *
     * @DI\InjectParams({
     *     "om"              = @DI\Inject("claroline.persistence.object_manager"),
     *     "questionManager" = @DI\Inject("ujm.exo.question_manager")
     * })
     *
     * @param ObjectManager   $om
     * @param QuestionManager $questionManager
     */
    public function __construct(
        ObjectManager $om,
        QuestionManager $questionManager)
    {
        $this->om = $om;
        $this->questionManager = $questionManager;
    }

    /**
     * Append a Question to a Step.
     *
     * @param Step     $step
     * @param Question $question
     * @param int      $order    if -1 the question will be added at the end of the Step
     */
    public function addQuestion(Step $step, Question $question, $order = -1)
    {
        $stepQuestion = new StepQuestion();

        $stepQuestion->setStep($step);
        $stepQuestion->setQuestion($question);

        if (-1 === $order) {
            // Calculate current Question order
            $order = count($step->getStepQuestions());
        }

        $stepQuestion->setOrdre($order);

        $this->om->persist($stepQuestion);
        $this->om->flush();
    }

    /**
     * Reorder the Questions of a Step.
     *
     * @param Step  $step
     * @param array $order an ordered array of Question IDs
     *
     * @return array array of errors if something went wrong
     */
    public function reorderQuestions(Step $step, array $order)
    {
        $reorderToo = []; // List of Steps we need to reorder too (because we have transferred some Questions)
        foreach ($order as $pos => $questionId) {
            /** @var StepQuestion $stepQuestion */
            $stepQuestion = $this->om->getRepository('UJMExoBundle:StepQuestion')->findByExerciseAndQuestion($step->getExercise(), $questionId);
            if (!$stepQuestion) {
                // Question is not linked to the Exercise, there is a problem with the order array
                return [
                    'message' => 'Can not reorder the Question. Unknown question found.',
                ];
            }

            $oldStep = $stepQuestion->getStep();
            if ($oldStep !== $step) {
                // The question comes from another Step => destroy old link and create a new One
                $oldStep->removeStepQuestion($stepQuestion);

                $stepQuestion->setStep($step);

                $reorderToo[] = $oldStep;
            }

            // Update order
            $stepQuestion->setOrdre($pos);

            $this->om->persist($stepQuestion);
        }

        if (!empty($reorderToo)) {
            // In fact as the client call the server each time a Question is moved, there will be always one Step in this array
            /** @var Step $stepToReorder */
            foreach ($reorderToo as $stepToReorder) {
                $stepQuestions = $stepToReorder->getStepQuestions();
                /** @var StepQuestion $sqToReorder */
                foreach ($stepQuestions as $pos => $sqToReorder) {
                    $sqToReorder->setOrdre($pos);
                }
            }
        }

        $this->om->flush();

        return [];
    }

    /**
     * Create a copy of a Step.
     *
     * @param Step $step
     *
     * @return Step the copy of the Step
     */
    public function copyStep(Step $step)
    {
        $newStep = new Step();

        // Populate Step properties
        $newStep->setOrder($step->getOrder());
        $newStep->setText($step->getText());
        $newStep->setNbQuestion($step->getNbQuestion());
        $newStep->setShuffle($step->getShuffle());
        $newStep->setDuration($step->getDuration());
        $newStep->setMaxAttempts($step->getMaxAttempts());
        $newStep->setKeepSameQuestion($step->getKeepSameQuestion());

        // Link questions to Step
        /** @var StepQuestion $stepQuestion */
        foreach ($step->getStepQuestions() as $stepQuestion) {
            $newStepQuestion = new StepQuestion();

            $newStepQuestion->setStep($newStep);
            $newStepQuestion->setQuestion($stepQuestion->getQuestion());
            $newStepQuestion->setOrdre($stepQuestion->getOrdre());
        }

        return $newStep;
    }

    /**
     * Exports a step in a JSON-encodable format.
     *
     * @param Step $step
     * @param bool $withSolutions
     *
     * @return array
     */
    public function exportStep(Step $step, $withSolutions = true)
    {
        $stepQuestions = $step->getStepQuestions();

        $items = [];

        /** @var StepQuestion $stepQuestion */
        foreach ($stepQuestions as $stepQuestion) {
            $question = $stepQuestion->getQuestion();
            $items[] = $this->questionManager->exportQuestion($question, $withSolutions);
        }

        return [
            'id' => $step->getId(),
            'maxAttempts' => $step->getMaxAttempts(),
            'items' => $items,
        ];
    }
}
