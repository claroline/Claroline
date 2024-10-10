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
     */
    abstract protected function getQuestionValidator(): ValidatorInterface;

    /**
     * Gets the answer Validator instance.
     */
    abstract protected function getAnswerValidator(): ValidatorInterface;

    /**
     * Gets the question Serializer instance.
     */
    abstract protected function getQuestionSerializer(): object;

    /**
     * Validates a question data.
     */
    public function validateQuestion(array $question, array $options = []): array
    {
        return $this->getQuestionValidator()->validate($question, $options);
    }

    /**
     * Validates the answer data for a question.
     */
    public function validateAnswer(mixed $answer, AbstractItem $question, array $options = []): array
    {
        $options[Validation::QUESTION] = $question;

        return $this->getAnswerValidator()->validate($answer, $options);
    }

    /**
     * Serializes a question entity.
     */
    public function serializeQuestion(AbstractItem $question, array $options = []): array
    {
        return $this->getQuestionSerializer()->serialize($question, $options);
    }

    /**
     * Deserializes question data.
     */
    public function deserializeQuestion(array $data, AbstractItem $question = null, array $options = []): AbstractItem
    {
        return $this->getQuestionSerializer()->deserialize($data, $question, $options);
    }

    public function getCsvTitles(AbstractItem $question): array
    {
        if (!empty($question->getQuestion()->getTitle())) {
            return [$question->getQuestion()->getTitle()];
        }

        return [$question->getQuestion()->getContentText()];
    }

    public function getCsvAnswers(AbstractItem $question, Answer $answer): array
    {
        return ['nope'];
    }
}
