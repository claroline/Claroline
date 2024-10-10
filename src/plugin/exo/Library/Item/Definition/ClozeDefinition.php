<?php

namespace UJM\ExoBundle\Library\Item\Definition;

use UJM\ExoBundle\Entity\Attempt\Answer;
use UJM\ExoBundle\Entity\ItemType\AbstractItem;
use UJM\ExoBundle\Entity\ItemType\ClozeQuestion;
use UJM\ExoBundle\Entity\Misc\Hole;
use UJM\ExoBundle\Entity\Misc\Keyword;
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
    public function __construct(
        private readonly ClozeQuestionValidator $validator,
        private readonly ClozeAnswerValidator $answerValidator,
        private readonly ClozeQuestionSerializer $serializer
    ) {
    }

    public static function getMimeType(): string
    {
        return ItemType::CLOZE;
    }

    public static function getEntityClass(): string
    {
        return ClozeQuestion::class;
    }

    protected function getQuestionValidator(): ClozeQuestionValidator
    {
        return $this->validator;
    }

    protected function getAnswerValidator(): ClozeAnswerValidator
    {
        return $this->answerValidator;
    }

    protected function getQuestionSerializer(): ClozeQuestionSerializer
    {
        return $this->serializer;
    }

    /**
     * @param ClozeQuestion $question
     */
    public function correctAnswer(AbstractItem $question, mixed $answer): CorrectedAnswer
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
     */
    public function expectAnswer(AbstractItem $question): array
    {
        return array_map(function (Hole $hole) {
            return $this->findHoleExpectedAnswer($hole);
        }, $question->getHoles()->toArray());
    }

    /**
     * @param ClozeQuestion $question
     */
    public function allAnswers(AbstractItem $question): array
    {
        $answers = [];
        foreach ($question->getHoles() as $hole) {
            $answers = array_merge($answers, $hole->getKeywords()->toArray());
        }

        return $answers;
    }

    /**
     * @param ClozeQuestion $question
     */
    public function getStatistics(AbstractItem $question, array $answersData, int $total): array
    {
        $holes = [];
        $answered = [];
        $nbUnanswered = $total - count($answersData);

        // Create an array with holeId => holeObject for easy search
        $holesMap = [];
        /** @var Hole $hole */
        foreach ($question->getHoles() as $hole) {
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
        foreach ($question->getHoles() as $hole) {
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
     * @param ClozeQuestion $question
     */
    public function refreshIdentifiers(AbstractItem $question): void
    {
        $text = $question->getText();

        /** @var Hole $hole */
        foreach ($question->getHoles() as $hole) {
            // stash current id
            $oldId = $hole->getUuid();

            // generate new id for hole
            $hole->refreshUuid();

            // replace placeholder in text
            $text = str_replace('[['.$oldId.']]', '[['.$hole->getUuid().']]', $text);
        }

        $question->setText($text);
    }

    private function findHoleExpectedAnswer(Hole $hole): ?Keyword
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
     * @param ClozeQuestion $question
     */
    public function getCsvTitles(AbstractItem $question): array
    {
        $qText = $question->getQuestion()->getTitle() ?? $question->getQuestion()->getContent();

        return array_map(function (Hole $hole) use ($qText) {
            return $qText.': hole-'.$hole->getUuid();
        }, $question->getHoles()->toArray());
    }

    /**
     * @param ClozeQuestion $question
     */
    public function getCsvAnswers(AbstractItem $question, Answer $answer): array
    {
        $data = json_decode($answer->getData(), true);
        $answers = [];
        $answeredHoles = [];

        if (is_array($data)) {
            foreach ($data as $answer) {
                $answeredHoles[$answer['holeId']] = $answer['answerText'];
            }
        }

        foreach ($question->getHoles() as $hole) {
            (array_key_exists($hole->getUuid(), $answeredHoles)) ?
              $answers[] = $answeredHoles[$hole->getUuid()] :
              $answers[] = null;
        }

        return $answers;
    }
}
