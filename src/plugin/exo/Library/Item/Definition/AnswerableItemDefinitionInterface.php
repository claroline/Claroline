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
     */
    public function validateAnswer(mixed $answer, AbstractItem $question, array $options = []): array;

    /**
     * Corrects an answer submitted to a question.
     * This method formats the user answers into an array that can be used to calculate the obtained score.
     * The outputted array MUST have the following structure.
     */
    public function correctAnswer(AbstractItem $question, mixed $answer): ?CorrectedAnswer;

    /**
     * Returns the expected answers of the question.
     *
     * @return AnswerPartInterface[]
     */
    public function expectAnswer(AbstractItem $question): array;

    /**
     * Returns all the defined answers of the question.
     *
     * @return AnswerPartInterface[]
     */
    public function allAnswers(AbstractItem $question): array;

    /**
     * Gets statistics on answers given to a question.
     */
    public function getStatistics(AbstractItem $question, array $answersData, int $total): array;

    public function getCsvTitles(AbstractItem $question): array;

    public function getCsvAnswers(AbstractItem $question, Answer $answer): array;
}
