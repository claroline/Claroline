<?php

namespace UJM\ExoBundle\Library\Item\Definition;

use UJM\ExoBundle\Entity\ItemType\AbstractItem;
use UJM\ExoBundle\Library\Attempt\AnswerPartInterface;
use UJM\ExoBundle\Library\Attempt\CorrectedAnswer;

/**
 * Interface for the definition of a question type.
 */
interface ItemDefinitionInterface
{
    /**
     * Gets the mime type of the question.
     *
     * It MUST have the format : application/x.{QUESTION_TYPE}+json
     *
     * @return string
     */
    public static function getMimeType();

    /**
     * Gets the entity class holding the specific question data.
     *
     * This method needs to only return the class name, without namespace (eg. ChoiceQuestion).
     * The full namespace `UJM\ExoBundle\Entity\ItemType` is added as prefix to the return value.
     *
     * @return string
     */
    public static function getEntityClass();

    /**
     * Validates question data.
     *
     * @param \stdClass $question
     * @param array     $options
     *
     * @return array
     */
    public function validateQuestion(\stdClass $question, array $options = []);

    /**
     * Serializes question entity.
     *
     * @param AbstractItem $question
     * @param array        $options
     *
     * @return \stdClass
     */
    public function serializeQuestion(AbstractItem $question, array $options = []);

    /**
     * Deserializes question data.
     *
     * @param \stdClass    $data
     * @param AbstractItem $question
     * @param array        $options
     *
     * @return AbstractItem
     */
    public function deserializeQuestion(\stdClass $data, AbstractItem $question = null, array $options = []);

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
