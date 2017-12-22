<?php

namespace UJM\ExoBundle\Library\Item\Definition;

use UJM\ExoBundle\Entity\Attempt\Answer;
use UJM\ExoBundle\Entity\ItemType\AbstractItem;
use UJM\ExoBundle\Library\Options\Validation;
use UJM\ExoBundle\Library\Serializer\SerializerInterface;
use UJM\ExoBundle\Library\Validator\ValidatorInterface;

/**
 * Base class for question definitions.
 * Permits to use separate classes to handle Serialization and Validation.
 */
abstract class AbstractDefinition implements ItemDefinitionInterface, ExportableCsvAnswerInterface, AnswerableItemDefinitionInterface
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
     *
     * @return SerializerInterface
     */
    abstract protected function getQuestionSerializer();

    /**
     * Validates a choice question.
     *
     * @param \stdClass $question
     * @param array     $options
     *
     * @return array
     */
    public function validateQuestion(\stdClass $question, array $options = [])
    {
        return $this->getQuestionValidator()->validate($question, $options);
    }

    /**
     * Validates the answer data for a question.
     *
     * @param mixed        $answer
     * @param AbstractItem $question
     * @param array        $options
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
     * @param AbstractItem $question
     * @param array        $options
     *
     * @return \stdClass
     */
    public function serializeQuestion(AbstractItem $question, array $options = [])
    {
        return $this->getQuestionSerializer()->serialize($question, $options);
    }

    /**
     * Deserializes question data.
     *
     * @param \stdClass    $questionData
     * @param AbstractItem $question
     * @param array        $options
     *
     * @return AbstractItem
     */
    public function deserializeQuestion(\stdClass $questionData, AbstractItem $question = null, array $options = [])
    {
        return $this->getQuestionSerializer()->deserialize($questionData, $question, $options);
    }

    public function getCsvTitles(AbstractItem $question)
    {
        return [$question->getTitle()];
    }

    public function getCsvAnswers(AbstractItem $question, Answer $answer)
    {
        return ['nope'];
    }
}
