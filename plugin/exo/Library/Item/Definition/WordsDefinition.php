<?php

namespace UJM\ExoBundle\Library\Item\Definition;

use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Entity\ItemType\AbstractItem;
use UJM\ExoBundle\Entity\ItemType\OpenQuestion;
use UJM\ExoBundle\Entity\Misc\Keyword;
use UJM\ExoBundle\Library\Attempt\CorrectedAnswer;
use UJM\ExoBundle\Library\Item\ItemType;
use UJM\ExoBundle\Serializer\Item\Type\WordsQuestionSerializer;
use UJM\ExoBundle\Transfer\Parser\ContentParserInterface;
use UJM\ExoBundle\Validator\JsonSchema\Attempt\AnswerData\WordsAnswerValidator;
use UJM\ExoBundle\Validator\JsonSchema\Item\Type\WordsQuestionValidator;

/**
 * Words question definition.
 *
 * @DI\Service("ujm_exo.definition.question_words")
 * @DI\Tag("ujm_exo.definition.item")
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
     *
     * @DI\InjectParams({
     *     "validator"       = @DI\Inject("ujm_exo.validator.question_words"),
     *     "answerValidator" = @DI\Inject("ujm_exo.validator.answer_words"),
     *     "serializer"      = @DI\Inject("ujm_exo.serializer.question_words")
     * })
     */
    public function __construct(
        WordsQuestionValidator $validator,
        WordsAnswerValidator $answerValidator,
        WordsQuestionSerializer $serializer)
    {
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
     * @return array
     */
    public function expectAnswer(AbstractItem $question)
    {
        return array_filter($question->getKeywords()->toArray(), function (Keyword $keyword) {
            return 0 < $keyword->getScore();
        });
    }

    /**
     * @param OpenQuestion $wordsQuestion
     * @param array        $answersData
     *
     * @return array
     */
    public function getStatistics(AbstractItem $wordsQuestion, array $answersData)
    {
        $keywords = [];

        foreach ($answersData as $answerData) {
            /** @var Keyword $keyword */
            foreach ($wordsQuestion->getKeywords() as $keyword) {
                if ($this->containKeyword($answerData, $keyword)) {
                    if (!isset($keywords[$keyword->getId()])) {
                        // First answer to contain the keyword
                        $keywords[$keyword->getId()] = new \stdClass();
                        $keywords[$keyword->getId()]->id = $keyword->getId();
                        $keywords[$keyword->getId()]->count = 0;
                    }

                    ++$keywords[$keyword->getId()]->count;
                }
            }
        }

        return array_values($keywords);
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

    private function containKeyword($string, Keyword $keyword)
    {
        $found = false;

        $flags = $keyword->isCaseSensitive() ? 'i' : '';
        if (1 === preg_match('/'.$keyword->getText().'/'.$flags, $string)) {
            $found = true;
        }

        return $found;
    }
}
