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
     * @param ObjectManager $om
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
     * Append a Question to a Step
     * @param Step $step
     * @param Question $question
     * @param int $order if -1 the question will be added at the end of the Step
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
