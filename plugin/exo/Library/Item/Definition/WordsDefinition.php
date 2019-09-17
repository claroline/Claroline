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
    /**
     * @var WordsQuestionValidator
     */
    private $validator;

    /**
     * @var WordsAnswerValidator
     */
    private $answerValidator;

    /**
     * @var WordsQuestionSerializer
     */
    private $serializer;

    /**
     * WordsDefinition constructor.
     *
     * @param WordsQuestionValidator  $validator
     * @param WordsAnswerValidator    $answerValidator
     * @param WordsQuestionSerializer $serializer
     */
    public function __construct(
        WordsQuestionValidator $validator,
        WordsAnswerValidator $answerValidator,
        WordsQuestionSerializer $serializer
    ) {
        $this->validator = $validator;
        $this->answerValidator = $answerValidator;
        $this->serializer = $serializer;
    }

    /**
     * Gets the words question mime-type.
     *
     * @return string
     */
    public static function getMimeType()
    {
        return ItemType::WORDS;
    }

    /**
     * Gets the words question entity.
     *
     * @return string
     */
    public static function getEntityClass()
    {
        return '\UJM\ExoBundle\Entity\ItemType\OpenQuestion';
    }

    /**
     * Gets the words question validator.
     *
     * @return WordsQuestionValidator
     */
    protected function getQuestionValidator()
    {
        return $this->validator;
    }

    /**
     * Gets the words answer validator.
     *
     * @return WordsAnswerValidator
     */
    protected function getAnswerValidator()
    {
        return $this->answerValidator;
    }

    /**
     * Gets the words question serializer.
     *
     * @return WordsQuestionSerializer
     */
    protected function getQuestionSerializer()
    {
        return $this->serializer;
    }

    /**
     * @param OpenQuestion $question
     * @param string       $answer
     *
     * @return CorrectedAnswer
     */
    public function correctAnswer(AbstractItem $question, $answer)
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
     *
     * @return AnswerPartInterface[]
     */
    public function expectAnswer(AbstractItem $question)
    {
        return array_filter($question->getKeywords()->toArray(), function (Keyword $keyword) {
            return 0 < $keyword->getScore();
        });
    }

    /**
     * @param OpenQuestion $question
     *
     * @return AnswerPartInterface[]
     */
    public function allAnswers(AbstractItem $question)
    {
        return $question->getKeywords()->toArray();
    }

    /**
     * @param OpenQuestion $wordsQuestion
     * @param array        $answersData
     * @param int          $total
     *
     * @return array
     */
    public function getStatistics(AbstractItem $wordsQuestion, array $answersData, $total)
    {
        $words = [];

        foreach ($answersData as $answerData) {
            /** @var Keyword $keyword */
            foreach ($wordsQuestion->getKeywords() as $keyword) {
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
     * @param AbstractItem $item
     */
    public function refreshIdentifiers(AbstractItem $item)
    {
        return;
    }

    private function containKeyword($string, Keyword $keyword)
    {
        $found = false;

        $flags = $keyword->isCaseSensitive() ? 'i' : '';
        if (1 === preg_match('/'.$keyword->getText().'/'.$flags, $string)) {
            $found = true;
        }

        return $found;
    }

    public function getCsvAnswers(AbstractItem $item, Answer $answer)
    {
        return [json_decode($answer->getData(), true)];
    }
}
