<?php

namespace UJM\ExoBundle\Library\Item\Definition;

use UJM\ExoBundle\Entity\Attempt\Answer;
use UJM\ExoBundle\Entity\ItemType\AbstractItem;
use UJM\ExoBundle\Entity\ItemType\ClozeQuestion;
use UJM\ExoBundle\Entity\Misc\Hole;
use UJM\ExoBundle\Entity\Misc\Keyword;
use UJM\ExoBundle\Library\Attempt\AnswerPartInterface;
use UJM\ExoBundle\Library\Attempt\CorrectedAnswer;
use UJM\ExoBundle\Library\Item\ItemType;
use UJM\ExoBundle\Serializer\Item\Type\ClozeQuestionSerializer;
use UJM\ExoBundle\Validator\JsonSchema\Attempt\AnswerData\ClozeAnswerValidator;
use UJM\ExoBundle\Validator\JsonSchema\Item\Type\ClozeQuestionValidator;

/**
 * Cloze question definition.
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
                $hole = $question->getHole($holeAnswer['holeId']);
                if ($hole) {
                    $keyword = $hole->getKeyword($holeAnswer['answerText']);
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
     * @return AnswerPartInterface[]
     */
    public function expectAnswer(AbstractItem $question)
    {
        return array_map(function (Hole $hole) {
            return $this->findHoleExpectedAnswer($hole);
        }, $question->getHoles()->toArray());
    }

    /**
     * @param ClozeQuestion $question
     *
     * @return AnswerPartInterface[]
     */
    public function allAnswers(AbstractItem $question)
    {
        $answers = [];
        foreach ($question->getHoles() as $hole) {
            $answers = array_merge($answers, $hole->getKeywords()->toArray());
        }

        return $answers;
    }

    /**
     * @param ClozeQuestion $clozeQuestion
     *
     * @return array
     */
    public function getStatistics(AbstractItem $clozeQuestion, array $answersData, $total)
    {
        $holes = [];
        $answered = [];
        $nbUnanswered = $total - count($answersData);

        // Create an array with holeId => holeObject for easy search
        $holesMap = [];
        /** @var Hole $hole */
        foreach ($clozeQuestion->getHoles() as $hole) {
            $holesMap[$hole->getUuid()] = $hole;
            $answered[$hole->getUuid()] = 0;
        }

        foreach ($answersData as $answerData) {
            foreach ($answerData as $holeAnswer) {
                if (!empty($holeAnswer['answerText'])) {
                    $answered[$holeAnswer['holeId']] = isset($answered[$holeAnswer['holeId']]) ?
                        $answered[$holeAnswer['holeId']] + 1 :
                        1;

                    if (!isset($holes[$holeAnswer['holeId']])) {
                        $holes[$holeAnswer['holeId']] = [];
                    }

                    $keyword = isset($holesMap[$holeAnswer['holeId']]) ?
                        $holesMap[$holeAnswer['holeId']]->getKeyword($holeAnswer['answerText']) :
                        null;

                    if ($keyword) {
                        $holes[$holeAnswer['holeId']][$keyword->getText()] = isset($holes[$holeAnswer['holeId']][$keyword->getText()]) ?
                            $holes[$holeAnswer['holeId']][$keyword->getText()] + 1 :
                            1;
                    } else {
                        $holes[$holeAnswer['holeId']]['_others'] = isset($holes[$holeAnswer['holeId']]['_others']) ?
                            $holes[$holeAnswer['holeId']]['_others'] + 1 :
                            1;
                    }
                }
            }
        }
        foreach ($clozeQuestion->getHoles() as $hole) {
            $holeId = $hole->getUuid();

            if (0 < count($answersData) - $answered[$holeId]) {
                if (!isset($holes[$holeId])) {
                    $holes[$holeId] = [];
                }
                $holes[$holeId]['_unanswered'] = count($answersData) - $answered[$holeId];
            }
        }

        return [
            'holes' => $holes,
            'total' => $total,
            'unanswered' => $nbUnanswered,
        ];
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

    public function getCsvTitles(AbstractItem $item)
    {
        $qText = $item->getQuestion()->getTitle() ?? $item->getQuestion()->getContent();

        return array_map(function (Hole $hole) use ($qText) {
            return $qText.': hole-'.$hole->getUuid();
        }, $item->getHoles()->toArray());
    }

    public function getCsvAnswers(AbstractItem $item, Answer $answer)
    {
        $data = json_decode($answer->getData(), true);
        $answers = [];
        $answeredHoles = [];

        if (is_array($data)) {
            foreach ($data as $answer) {
                $answeredHoles[$answer['holeId']] = $answer['answerText'];
            }
        }

        foreach ($item->getHoles() as $hole) {
            (array_key_exists($hole->getUuid(), $answeredHoles)) ?
              $answers[] = $answeredHoles[$hole->getUuid()] :
              $answers[] = null;
        }

        return $answers;
    }
}
