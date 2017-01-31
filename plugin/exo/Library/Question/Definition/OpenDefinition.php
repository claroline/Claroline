<?php

namespace UJM\ExoBundle\Library\Question\Definition;

use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Entity\QuestionType\AbstractQuestion;
use UJM\ExoBundle\Library\Question\QuestionType;
use UJM\ExoBundle\Serializer\Question\Type\OpenQuestionSerializer;
use UJM\ExoBundle\Validator\JsonSchema\Attempt\AnswerData\OpenAnswerValidator;
use UJM\ExoBundle\Validator\JsonSchema\Question\Type\OpenQuestionValidator;

/**
 * Open question definition.
 *
 * @DI\Service("ujm_exo.definition.question_open")
 * @DI\Tag("ujm_exo.definition.question")
 */
class OpenDefinition extends AbstractDefinition
{
    /**
     * @var OpenQuestionValidator
     */
    private $validator;

    /**
     * @var OpenAnswerValidator
     */
    private $answerValidator;

    /**
     * @var OpenQuestionSerializer
     */
    private $serializer;

    /**
     * OpenDefinition constructor.
     *
     * @param OpenQuestionValidator  $validator
     * @param OpenAnswerValidator    $answerValidator
     * @param OpenQuestionSerializer $serializer
     *
     * @DI\InjectParams({
     *     "validator"       = @DI\Inject("ujm_exo.validator.question_open"),
     *     "answerValidator" = @DI\Inject("ujm_exo.validator.answer_open"),
     *     "serializer"      = @DI\Inject("ujm_exo.serializer.question_open")
     * })
     */
    public function __construct(
        OpenQuestionValidator $validator,
        OpenAnswerValidator $answerValidator,
        OpenQuestionSerializer $serializer)
    {
        $this->validator = $validator;
        $this->answerValidator = $answerValidator;
        $this->serializer = $serializer;
    }

    /**
     * Gets the open question mime-type.
     *
     * @return string
     */
    public static function getMimeType()
    {
        return QuestionType::OPEN;
    }

    /**
     * Gets the open question entity.
     *
     * @return string
     */
    public static function getEntityClass()
    {
        return '\UJM\ExoBundle\Entity\QuestionType\OpenQuestion';
    }

    /**
     * Gets the open question validator.
     *
     * @return OpenQuestionValidator
     */
    protected function getQuestionValidator()
    {
        return $this->validator;
    }

    /**
     * Gets the open answer validator.
     *
     * @return OpenAnswerValidator
     */
    protected function getAnswerValidator()
    {
        return $this->answerValidator;
    }

    /**
     * Gets the open question serializer.
     *
     * @return OpenQuestionSerializer
     */
    protected function getQuestionSerializer()
    {
        return $this->serializer;
    }

    /**
     * Not implemented for open questions as it's not auto corrected.
     *
     * @param AbstractQuestion $question
     * @param $answer
     *
     * @return bool
     */
    public function correctAnswer(AbstractQuestion $question, $answer)
    {
        return false;
    }

    /**
     * Not implemented for open questions as it's not auto corrected.
     *
     * @param AbstractQuestion $question
     *
     * @return array
     */
    public function expectAnswer(AbstractQuestion $question)
    {
        return [];
    }

    public function getStatistics(AbstractQuestion $openQuestion, array $answers)
    {
        return [];
    }
}
