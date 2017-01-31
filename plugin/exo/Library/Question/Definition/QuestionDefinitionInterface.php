<?php

namespace UJM\ExoBundle\Library\Question\Definition;

use UJM\ExoBundle\Entity\QuestionType\AbstractQuestion;
use UJM\ExoBundle\Library\Attempt\AnswerPartInterface;
use UJM\ExoBundle\Library\Attempt\CorrectedAnswer;

/**
 * Interface for the definition of a question type.
 */
interface QuestionDefinitionInterface
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
     * The full namespace `UJM\ExoBundle\Entity\QuestionType` is added as prefix to the return value.
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
     * @param AbstractQuestion $question
     * @param array            $options
     *
     * @return \stdClass
     */
    public function serializeQuestion(AbstractQuestion $question, array $options = []);

    /**
     * Deserializes question data.
     *
     * @param \stdClass        $data
     * @param AbstractQuestion $question
     * @param array            $options
     *
     * @return AbstractQuestion
     */
    public function deserializeQuestion(\stdClass $data, AbstractQuestion $question = null, array $options = []);

    /**
     * Validates question answer.
     *
     * @param mixed            $answer
     * @param AbstractQuestion $question
     * @param array            $options
     *
     * @return array
     */
    public function validateAnswer($answer, AbstractQuestion $question, array $options = []);

    /**
     * Corrects an answer submitted to a question.
     * This method formats the user answers into an array that can be used to calculate the obtained score.
     * The outputted array MUST have the following structure.
     *
     * @param AbstractQuestion $question
     * @param $answer
     *
     * @return CorrectedAnswer
     */
    public function correctAnswer(AbstractQuestion $question, $answer);

    /**
     * Returns the expected answers of the question.
     *
     * @param AbstractQuestion $question
     *
     * @return AnswerPartInterface[]
     */
    public function expectAnswer(AbstractQuestion $question);

    /**
     * Gets statistics on answers given to a question.
     *
     * @param AbstractQuestion $question
     * @param array            $answersData
     *
     * @return \stdClass
     */
    public function getStatistics(AbstractQuestion $question, array $answersData);
}
