<?php

namespace UJM\ExoBundle\Manager\Attempt;

use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Entity\Attempt\Answer;
use UJM\ExoBundle\Entity\Item\Item;
use UJM\ExoBundle\Library\Options\Validation;
use UJM\ExoBundle\Library\Validator\ValidationException;
use UJM\ExoBundle\Serializer\Attempt\AnswerSerializer;
use UJM\ExoBundle\Validator\JsonSchema\Attempt\AnswerValidator;

/**
 * AnswerManager manages answers made by users to questions.
 *
 * @DI\Service("ujm_exo.manager.answer")
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
     *
     * @DI\InjectParams({
     *     "om"         = @DI\Inject("claroline.persistence.object_manager"),
     *     "validator"  = @DI\Inject("ujm_exo.validator.answer"),
     *     "serializer" = @DI\Inject("ujm_exo.serializer.answer")
     * })
     *
     * @param ObjectManager    $om
     * @param AnswerValidator  $validator
     * @param AnswerSerializer $serializer
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
     * @param Item      $question
     * @param \stdClass $answerData
     *
     * @return Answer
     *
     * @throws ValidationException
     */
    public function create(Item $question, \stdClass $answerData)
    {
        return $this->update($question, new Answer(), $answerData);
    }

    /**
     * Validates and updates an Answer entity with raw data.
     *
     * @param Item      $question
     * @param Answer    $answer
     * @param \stdClass $answerData
     * @param bool      $noFlush
     *
     * @return Answer
     *
     * @throws ValidationException
     */
    public function update(Item $question, Answer $answer, \stdClass $answerData, $noFlush = false)
    {
        $errors = $this->validator->validate($answerData, [Validation::QUESTION => $question->getInteraction()]);
        if (count($errors) > 0) {
            throw new ValidationException('Answer is not valid', $errors);
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
     * @param Answer $answer
     * @param array  $options
     *
     * @return \stdClass
     */
    public function serialize(Answer $answer, array $options = [])
    {
        return $this->serializer->serialize($answer, $options);
    }
}
