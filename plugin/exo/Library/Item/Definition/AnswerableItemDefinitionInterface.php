<?php

namespace UJM\ExoBundle\Library\Item\Definition;

use UJM\ExoBundle\Entity\ItemType\AbstractItem;
use UJM\ExoBundle\Library\Attempt\AnswerPartInterface;
use UJM\ExoBundle\Library\Attempt\CorrectedAnswer;

/**
 * Interface for the definition of a question type.
 */
interface AnswerableItemDefinitionInterface
{
    /**
     * Validates question answer.
     *
     * @param mixed        $answer
     * @param AbstractItem $question
     * @param array        $options
     *
     * @return array
     */
    public function validateAnswer($answer, AbstractItem $question, array $options = []);

    /**
     * Corrects an answer submitted to a question.
     * This method formats the user answers into an array that can be used to calculate the obtained score.
     * The outputted array MUST have the following structure.
     *
     * @param AbstractItem $question
     * @param $answer
     *
     * @return CorrectedAnswer
     */
    public function correctAnswer(AbstractItem $question, $answer);

    /**
     * Returns the expected answers of the question.
     *
     * @param AbstractItem $question
     *
     * @return AnswerPartInterface[]
     */
    public function expectAnswer(AbstractItem $question);

    /**
     * Gets statistics on answers given to a question.
     *
     * @param AbstractItem $question
     * @param array        $answersData
     *
     * @return \stdClass
     */
    public function getStatistics(AbstractItem $question, array $answersData);
}
