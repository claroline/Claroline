<?php

namespace UJM\ExoBundle\Manager;

use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Entity\Step;
use UJM\ExoBundle\Entity\StepQuestion;

/**
 * @DI\Service("ujm.exo.step_manager")
 */
class StepManager
{
    /**
     * StepManager constructor.
     *
     * @DI\InjectParams({
     *     "questionManager" = @DI\Inject("ujm.exo.question_manager")
     * })
     *
     * @param QuestionManager $questionManager
     */
    public function __construct(QuestionManager $questionManager)
    {
        $this->questionManager = $questionManager;
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
