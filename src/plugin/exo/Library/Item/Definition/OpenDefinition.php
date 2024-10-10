<?php

namespace UJM\ExoBundle\Library\Item\Definition;

use UJM\ExoBundle\Entity\Attempt\Answer;
use UJM\ExoBundle\Entity\ItemType\AbstractItem;
use UJM\ExoBundle\Entity\ItemType\OpenQuestion;
use UJM\ExoBundle\Library\Attempt\CorrectedAnswer;
use UJM\ExoBundle\Library\Item\ItemType;
use UJM\ExoBundle\Serializer\Item\Type\OpenQuestionSerializer;
use UJM\ExoBundle\Validator\JsonSchema\Attempt\AnswerData\OpenAnswerValidator;
use UJM\ExoBundle\Validator\JsonSchema\Item\Type\OpenQuestionValidator;

/**
 * Open question definition.
 */
class OpenDefinition extends AbstractDefinition
{
    public function __construct(
        private readonly OpenQuestionValidator $validator,
        private readonly OpenAnswerValidator $answerValidator,
        private readonly OpenQuestionSerializer $serializer
    ) {
    }

    public static function getMimeType(): string
    {
        return ItemType::OPEN;
    }

    public static function getEntityClass(): string
    {
        return OpenQuestion::class;
    }

    protected function getQuestionValidator(): OpenQuestionValidator
    {
        return $this->validator;
    }

    protected function getAnswerValidator(): OpenAnswerValidator
    {
        return $this->answerValidator;
    }

    protected function getQuestionSerializer(): OpenQuestionSerializer
    {
        return $this->serializer;
    }

    /**
     * Not implemented for open questions as it's not autocorrected.
     */
    public function correctAnswer(AbstractItem $question, $answer): ?CorrectedAnswer
    {
        return null;
    }

    /**
     * Not implemented for open questions as it's not autocorrected.
     */
    public function expectAnswer(AbstractItem $question): array
    {
        return [];
    }

    public function allAnswers(AbstractItem $question): array
    {
        return [];
    }

    /**
     * Not implemented because not relevant.
     */
    public function getStatistics(AbstractItem $question, array $answersData, int $total): array
    {
        return [];
    }

    /**
     * No additional identifier to regenerate.
     */
    public function refreshIdentifiers(AbstractItem $question): void
    {
    }

    public function getCsvAnswers(AbstractItem $question, Answer $answer): array
    {
        return [json_decode($answer->getData(), true)];
    }
}
