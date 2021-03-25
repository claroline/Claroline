<?php

namespace UJM\ExoBundle\Manager\Attempt;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use UJM\ExoBundle\Entity\Attempt\Answer;
use UJM\ExoBundle\Entity\Item\Item;
use UJM\ExoBundle\Library\Options\Validation;
use UJM\ExoBundle\Serializer\Attempt\AnswerSerializer;
use UJM\ExoBundle\Validator\JsonSchema\Attempt\AnswerValidator;

/**
 * AnswerManager manages answers made by users to questions.
 */
class AnswerManager
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var AnswerValidator
     */
    private $validator;

    /**
     * @var AnswerSerializer
     */
    private $serializer;

    /**
     * AnswerManager constructor.
     */
    public function __construct(
        ObjectManager $om,
        AnswerValidator $validator,
        AnswerSerializer $serializer)
    {
        $this->om = $om;
        $this->validator = $validator;
        $this->serializer = $serializer;
    }

    /**
     * Validates and creates a new Answer from raw data.
     *
     * @return Answer
     *
     * @throws InvalidDataException
     */
    public function create(Item $question, array $answerData)
    {
        return $this->update($question, new Answer(), $answerData);
    }

    /**
     * Validates and updates an Answer entity with raw data.
     *
     * @param bool $noFlush
     *
     * @return Answer
     *
     * @throws InvalidDataException
     */
    public function update(Item $question, Answer $answer, array $answerData, $noFlush = false)
    {
        $errors = $this->validator->validate($answerData, [Validation::QUESTION => $question->getInteraction()]);
        if (count($errors) > 0) {
            throw new InvalidDataException('Answer is not valid', $errors);
        }

        // Update Answer with new data
        $this->serializer->deserialize($answerData, $answer);

        // Save to DB
        $this->om->persist($answer);

        if (!$noFlush) {
            $this->om->flush();
        }

        return $answer;
    }

    /**
     * Serializes an answer.
     *
     * @return array
     */
    public function serialize(Answer $answer, array $options = [])
    {
        return $this->serializer->serialize($answer, $options);
    }
}
