<?php

namespace UJM\ExoBundle\Library\Item\Definition;

use UJM\ExoBundle\Entity\Attempt\Answer;
use UJM\ExoBundle\Entity\ItemType\AbstractItem;
use UJM\ExoBundle\Library\Options\Validation;
use UJM\ExoBundle\Library\Validator\ValidatorInterface;

/**
 * Base class for question definitions.
 * Permits to use separate classes to handle Serialization and Validation.
 */
abstract class AbstractDefinition implements ItemDefinitionInterface, AnswerableItemDefinitionInterface
{
    /**
     * Gets the question Validator instance.
     *
     * @return ValidatorInterface
     */
    abstract protected function getQuestionValidator();

    /**
     * Gets the answer Validator instance.
     *
     * @return ValidatorInterface
     */
    abstract protected function getAnswerValidator();

    /**
     * Gets the question Serializer instance.
     */
    abstract protected function getQuestionSerializer();

    /**
     * Validates a choice question.
     *
     * @return array
     */
    public function validateQuestion(array $question, array $options = [])
    {
        return $this->getQuestionValidator()->validate($question, $options);
    }

    /**
     * Validates the answer data for a question.
     *
     * @param mixed $answer
     *
     * @return array
     */
    public function validateAnswer($answer, AbstractItem $question, array $options = [])
    {
        $options[Validation::QUESTION] = $question;

        return $this->getAnswerValidator()->validate($answer, $options);
    }

    /**
     * Serializes a question entity.
     *
     * @return array
     */
    public function serializeQuestion(AbstractItem $question, array $options = [])
    {
        return $this->getQuestionSerializer()->serialize($question, $options);
    }

    /**
     * Deserializes question data.
     *
     * @param AbstractItem $question
     *
     * @return AbstractItem
     */
    public function deserializeQuestion(array $questionData, AbstractItem $question = null, array $options = [])
    {
        return $this->getQuestionSerializer()->deserialize($questionData, $question, $options);
    }

    public function getCsvTitles(AbstractItem $question)
    {
        if (!empty($question->getQuestion()->getTitle())) {
            return [$question->getQuestion()->getTitle()];
        }

        return [$question->getQuestion()->getContentText()];
    }

    public function getCsvAnswers(AbstractItem $question, Answer $answer)
    {
        return ['nope'];
    }
}
