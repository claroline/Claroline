<?php

namespace UJM\ExoBundle\Library\Item\Definition;

use UJM\ExoBundle\Entity\Attempt\Answer;
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
     * @param mixed $answer
     *
     * @return array
     */
    public function validateAnswer($answer, AbstractItem $question, array $options = []);

    /**
     * Corrects an answer submitted to a question.
     * This method formats the user answers into an array that can be used to calculate the obtained score.
     * The outputted array MUST have the following structure.
     *
     * @param $answer
     *
     * @return CorrectedAnswer
     */
    public function correctAnswer(AbstractItem $question, $answer);

    /**
     * Returns the expected answers of the question.
     *
     * @return AnswerPartInterface[]
     */
    public function expectAnswer(AbstractItem $question);

    /**
     * Returns all the defined answers of the question.
     *
     * @return AnswerPartInterface[]
     */
    public function allAnswers(AbstractItem $question);

    /**
     * Gets statistics on answers given to a question.
     *
     * @param int $total
     *
     * @return array
     */
    public function getStatistics(AbstractItem $question, array $answersData, $total);

    public function getCsvTitles(AbstractItem $item);

    public function getCsvAnswers(AbstractItem $item, Answer $answer);
}
