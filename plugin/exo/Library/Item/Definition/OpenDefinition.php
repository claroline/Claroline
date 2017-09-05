<?php

namespace UJM\ExoBundle\Library\Item\Definition;

use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Entity\Attempt\Answer;
use UJM\ExoBundle\Entity\ItemType\AbstractItem;
use UJM\ExoBundle\Library\Item\ItemType;
use UJM\ExoBundle\Serializer\Item\Type\OpenQuestionSerializer;
use UJM\ExoBundle\Transfer\Parser\ContentParserInterface;
use UJM\ExoBundle\Validator\JsonSchema\Attempt\AnswerData\OpenAnswerValidator;
use UJM\ExoBundle\Validator\JsonSchema\Item\Type\OpenQuestionValidator;

/**
 * Open question definition.
 *
 * @DI\Service("ujm_exo.definition.question_open")
 * @DI\Tag("ujm_exo.definition.item")
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
        OpenQuestionSerializer $serializer
    ) {
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
        return ItemType::OPEN;
    }

    /**
     * Gets the open question entity.
     *
     * @return string
     */
    public static function getEntityClass()
    {
        return '\UJM\ExoBundle\Entity\ItemType\OpenQuestion';
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
     * @param AbstractItem $question
     * @param $answer
     *
     * @return bool
     */
    public function correctAnswer(AbstractItem $question, $answer)
    {
        return false;
    }

    /**
     * Not implemented for open questions as it's not auto corrected.
     *
     * @param AbstractItem $question
     *
     * @return array
     */
    public function expectAnswer(AbstractItem $question)
    {
        return [];
    }

    /**
     * Not implemented because not relevant.
     *
     * @param AbstractItem $openQuestion
     * @param array        $answers
     *
     * @return array
     */
    public function getStatistics(AbstractItem $openQuestion, array $answers)
    {
        return [];
    }

    /**
     * No additional identifier to regenerate.
     *
     * @param AbstractItem $item
     */
    public function refreshIdentifiers(AbstractItem $item)
    {
        return;
    }

    /**
     * No additional content fields to process.
     *
     * @param ContentParserInterface $contentParser
     * @param \stdClass              $item
     */
    public function parseContents(ContentParserInterface $contentParser, \stdClass $item)
    {
        return;
    }

    public function getCsvTitles(AbstractItem $item)
    {
        return ['open-'.$item->getQuestion()->getUuid()];
    }

    public function getCsvAnswers(AbstractItem $item, Answer $answer)
    {
        return [json_decode($answer->getData())];
    }
}
