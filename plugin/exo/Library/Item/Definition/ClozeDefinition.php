<?php

namespace UJM\ExoBundle\Library\Item\Definition;

use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Entity\Attempt\Answer;
use UJM\ExoBundle\Entity\ItemType\AbstractItem;
use UJM\ExoBundle\Entity\ItemType\ClozeQuestion;
use UJM\ExoBundle\Entity\Misc\Hole;
use UJM\ExoBundle\Entity\Misc\Keyword;
use UJM\ExoBundle\Library\Attempt\CorrectedAnswer;
use UJM\ExoBundle\Library\Item\ItemType;
use UJM\ExoBundle\Serializer\Item\Type\ClozeQuestionSerializer;
use UJM\ExoBundle\Transfer\Parser\ContentParserInterface;
use UJM\ExoBundle\Validator\JsonSchema\Attempt\AnswerData\ClozeAnswerValidator;
use UJM\ExoBundle\Validator\JsonSchema\Item\Type\ClozeQuestionValidator;

/**
 * Cloze question definition.
 *
 * @DI\Service("ujm_exo.definition.question_cloze")
 * @DI\Tag("ujm_exo.definition.item")
 */
class ClozeDefinition extends AbstractDefinition
{
    /**
     * @var ClozeQuestionValidator
     */
    private $validator;

    /**
     * @var ClozeAnswerValidator
     */
    private $answerValidator;

    /**
     * @var ClozeQuestionSerializer
     */
    private $serializer;

    /**
     * ClozeDefinition constructor.
     *
     * @param ClozeQuestionValidator  $validator
     * @param ClozeAnswerValidator    $answerValidator
     * @param ClozeQuestionSerializer $serializer
     *
     * @DI\InjectParams({
     *     "validator"       = @DI\Inject("ujm_exo.validator.question_cloze"),
     *     "answerValidator" = @DI\Inject("ujm_exo.validator.answer_cloze"),
     *     "serializer"      = @DI\Inject("ujm_exo.serializer.question_cloze")
     * })
     */
    public function __construct(
        ClozeQuestionValidator $validator,
        ClozeAnswerValidator $answerValidator,
        ClozeQuestionSerializer $serializer
    ) {
        $this->validator = $validator;
        $this->answerValidator = $answerValidator;
        $this->serializer = $serializer;
    }

    /**
     * Gets the cloze question mime-type.
     *
     * @return string
     */
    public static function getMimeType()
    {
        return ItemType::CLOZE;
    }

    /**
     * Gets the cloze question entity.
     *
     * @return string
     */
    public static function getEntityClass()
    {
        return '\UJM\ExoBundle\Entity\ItemType\ClozeQuestion';
    }

    /**
     * Gets the cloze question validator.
     *
     * @return ClozeQuestionValidator
     */
    protected function getQuestionValidator()
    {
        return $this->validator;
    }

    /**
     * Gets the cloze answer validator.
     *
     * @return ClozeAnswerValidator
     */
    protected function getAnswerValidator()
    {
        return $this->answerValidator;
    }

    /**
     * Gets the cloze question serializer.
     *
     * @return ClozeQuestionSerializer
     */
    protected function getQuestionSerializer()
    {
        return $this->serializer;
    }

    /**
     * @param ClozeQuestion $question
     * @param array         $answer
     *
     * @return CorrectedAnswer
     */
    public function correctAnswer(AbstractItem $question, $answer)
    {
        $corrected = new CorrectedAnswer();

        if (!is_null($answer)) {
            foreach ($answer as $holeAnswer) {
                $hole = $question->getHole($holeAnswer->holeId);
                if ($hole) {
                    $keyword = $hole->getKeyword($holeAnswer->answerText);
                    if (!empty($keyword)) {
                        if (0 < $keyword->getScore()) {
                            $corrected->addExpected($keyword);
                        } else {
                            $corrected->addUnexpected($keyword);
                        }
                    } else {
                        // Retrieve the best answer for the hole
                        $corrected->addMissing($this->findHoleExpectedAnswer($hole));
                    }
                }
            }
        } else {
            $holes = $question->getHoles();

            foreach ($holes as $hole) {
                $corrected->addMissing($this->findHoleExpectedAnswer($hole));
            }
        }

        return $corrected;
    }

    /**
     * @param ClozeQuestion $question
     *
     * @return array
     */
    public function expectAnswer(AbstractItem $question)
    {
        return array_map(function (Hole $hole) {
            return $this->findHoleExpectedAnswer($hole);
        }, $question->getHoles()->toArray());
    }

    /**
     * @param ClozeQuestion $clozeQuestion
     * @param array         $answersData
     *
     * @return array
     */
    public function getStatistics(AbstractItem $clozeQuestion, array $answersData)
    {
        // Create an array with holeId => holeObject for easy search
        $holesMap = [];
        /** @var Hole $hole */
        foreach ($clozeQuestion->getHoles() as $hole) {
            $holesMap[$hole->getUuid()] = $hole;
        }

        $holes = [];

        foreach ($answersData as $answerData) {
            foreach ($answerData as $holeAnswer) {
                if (!empty($holeAnswer->answerText)) {
                    if (!isset($holes[$holeAnswer->holeId])) {
                        $holes[$holeAnswer->holeId] = new \stdClass();
                        $holes[$holeAnswer->holeId]->id = $holeAnswer->holeId;
                        $holes[$holeAnswer->holeId]->answered = 0;

                        // Answers counters for each keyword of the hole
                        $holes[$holeAnswer->holeId]->keywords = [];
                    }

                    // Increment the hole answers count
                    ++$holes[$holeAnswer->holeId]->answered;

                    $keyword = isset($holesMap[$holeAnswer->holeId]) ? $holesMap[$holeAnswer->holeId]->getKeyword($holeAnswer->answerText) : null;
                    if ($keyword) {
                        if (!isset($holes[$holeAnswer->holeId]->keywords[$keyword->getId()])) {
                            // Initialize the Hole keyword counter if it's the first time we find it
                            $holes[$holeAnswer->holeId]->keywords[$keyword->getId()] = new \stdClass();
                            // caseSensitive & text is the primary key for api transfers
                            $holes[$holeAnswer->holeId]->keywords[$keyword->getId()]->caseSensitive = $keyword->isCaseSensitive();
                            $holes[$holeAnswer->holeId]->keywords[$keyword->getId()]->text = $keyword->getText();
                            $holes[$holeAnswer->holeId]->keywords[$keyword->getId()]->count = 0;
                        }

                        ++$holes[$holeAnswer->holeId]->keywords[$keyword->getId()]->count;

                        break;
                    }
                }
            }
        }

        return array_values($holes);
    }

    /**
     * Refreshes hole UUIDs and update placeholders in text.
     *
     * @param ClozeQuestion $item
     */
    public function refreshIdentifiers(AbstractItem $item)
    {
        $text = $item->getText();

        /** @var Hole $hole */
        foreach ($item->getHoles() as $hole) {
            // stash current id
            $oldId = $hole->getUuid();

            // generate new id for hole
            $hole->refreshUuid();

            // replace placeholder in text
            $text = str_replace('[['.$oldId.']]', '[['.$hole->getUuid().']]', $text);
        }

        $item->setText($text);
    }

    /**
     * @param Hole $hole
     *
     * @return Keyword|null
     */
    private function findHoleExpectedAnswer(Hole $hole)
    {
        $best = null;
        foreach ($hole->getKeywords() as $keyword) {
            /** @var Keyword $keyword */
            if (empty($best) || $best->getScore() < $keyword->getScore()) {
                $best = $keyword;
            }
        }

        return $best;
    }

    /**
     * Parses item text.
     *
     * @param ContentParserInterface $contentParser
     * @param \stdClass              $item
     */
    public function parseContents(ContentParserInterface $contentParser, \stdClass $item)
    {
        $item->text = $contentParser->parse($item->text);
    }

    public function getCsvTitles(AbstractItem $item)
    {
        return array_map(function (Hole $hole) {
            return 'hole-'.$hole->getUuid();
        }, $item->getHoles()->toArray());
    }

    public function getCsvAnswers(AbstractItem $item, Answer $answer)
    {
        $data = json_decode($answer->getData());
        $answers = [];
        $answeredHoles = [];

        foreach ($data as $answer) {
            $answeredHoles[$answer->holeId] = $answer->answerText;
        }

        foreach ($item->getHoles() as $hole) {
            (array_key_exists($hole->getUuid(), $answeredHoles)) ?
              $answers[] = $answeredHoles[$hole->getUuid()] :
              $answers[] = null;
        }

        return $answers;
    }
}
