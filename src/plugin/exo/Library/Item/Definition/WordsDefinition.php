<?php

namespace UJM\ExoBundle\Library\Item\Definition;

use UJM\ExoBundle\Entity\Attempt\Answer;
use UJM\ExoBundle\Entity\ItemType\AbstractItem;
use UJM\ExoBundle\Entity\ItemType\OpenQuestion;
use UJM\ExoBundle\Entity\Misc\Keyword;
use UJM\ExoBundle\Library\Attempt\AnswerPartInterface;
use UJM\ExoBundle\Library\Attempt\CorrectedAnswer;
use UJM\ExoBundle\Library\Item\ItemType;
use UJM\ExoBundle\Serializer\Item\Type\WordsQuestionSerializer;
use UJM\ExoBundle\Validator\JsonSchema\Attempt\AnswerData\WordsAnswerValidator;
use UJM\ExoBundle\Validator\JsonSchema\Item\Type\WordsQuestionValidator;

/**
 * Words question definition.
 */
class WordsDefinition extends AbstractDefinition
{
    public function __construct(
        private readonly WordsQuestionValidator $validator,
        private readonly WordsAnswerValidator $answerValidator,
        private readonly WordsQuestionSerializer $serializer
    ) {
    }

    public static function getMimeType(): string
    {
        return ItemType::WORDS;
    }

    public static function getEntityClass(): string
    {
        return OpenQuestion::class;
    }

    protected function getQuestionValidator(): WordsQuestionValidator
    {
        return $this->validator;
    }

    protected function getAnswerValidator(): WordsAnswerValidator
    {
        return $this->answerValidator;
    }

    protected function getQuestionSerializer(): WordsQuestionSerializer
    {
        return $this->serializer;
    }

    /**
     * @param OpenQuestion $question
     */
    public function correctAnswer(AbstractItem $question, mixed $answer): CorrectedAnswer
    {
        $corrected = new CorrectedAnswer();
        foreach ($question->getKeywords() as $keyword) {
            if ($this->containKeyword($answer, $keyword)) {
                if (0 < $keyword->getScore()) {
                    $corrected->addExpected($keyword);
                } else {
                    $corrected->addUnexpected($keyword);
                }
            } elseif (0 < $keyword->getScore()) {
                $corrected->addMissing($keyword);
            }
        }

        return $corrected;
    }

    /**
     * @param OpenQuestion $question
     */
    public function expectAnswer(AbstractItem $question): array
    {
        return array_filter($question->getKeywords()->toArray(), function (Keyword $keyword) {
            return 0 < $keyword->getScore();
        });
    }

    /**
     * @param OpenQuestion $question
     */
    public function allAnswers(AbstractItem $question): array
    {
        return $question->getKeywords()->toArray();
    }

    /**
     * @param OpenQuestion $question
     */
    public function getStatistics(AbstractItem $question, array $answersData, int $total): array
    {
        $words = [];

        foreach ($answersData as $answerData) {
            /** @var Keyword $keyword */
            foreach ($question->getKeywords() as $keyword) {
                if ($this->containKeyword($answerData, $keyword)) {
                    $words[$keyword->getText()] = isset($words[$keyword->getText()]) ? $words[$keyword->getText()] + 1 : 1;
                }
            }
        }

        return [
            'words' => $words,
            'total' => $total,
            'unanswered' => $total - count($answersData),
        ];
    }

    /**
     * No additional identifier to regenerate.
     *
     * @param OpenQuestion $question
     */
    public function refreshIdentifiers(AbstractItem $question): void
    {
    }

    private function containKeyword(string $string, Keyword $keyword, ?string $contentType = 'text'): bool
    {
        $found = false;

        switch ($contentType) {
            case 'date':
            case 'text':
            default:
                $flags = $keyword->isCaseSensitive() ? 'i' : '';
                if (1 === preg_match('/(?:\W|^)(\Q'.$keyword->getText().'\E)(?:\W|$)/'.$flags, $string)) {
                    $found = true;
                }
                break;
        }

        return $found;
    }

    /**
     * @param OpenQuestion $question
     */
    public function getCsvAnswers(AbstractItem $question, Answer $answer): array
    {
        return [json_decode($answer->getData(), true)];
    }
}
