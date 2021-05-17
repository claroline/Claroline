<?php

namespace UJM\ExoBundle\Library\Item\Definition;

use UJM\ExoBundle\Entity\Attempt\Answer;
use UJM\ExoBundle\Entity\ItemType\AbstractItem;
use UJM\ExoBundle\Entity\ItemType\GridQuestion;
use UJM\ExoBundle\Entity\Misc\Cell;
use UJM\ExoBundle\Entity\Misc\CellChoice;
use UJM\ExoBundle\Library\Attempt\AnswerPartInterface;
use UJM\ExoBundle\Library\Attempt\CorrectedAnswer;
use UJM\ExoBundle\Library\Attempt\GenericPenalty;
use UJM\ExoBundle\Library\Attempt\GenericScore;
use UJM\ExoBundle\Library\Csv\ArrayCompressor;
use UJM\ExoBundle\Library\Item\ItemType;
use UJM\ExoBundle\Serializer\Item\Type\GridQuestionSerializer;
use UJM\ExoBundle\Validator\JsonSchema\Attempt\AnswerData\GridAnswerValidator;
use UJM\ExoBundle\Validator\JsonSchema\Item\Type\GridQuestionValidator;

/**
 * Grid question definition.
 */
class GridDefinition extends AbstractDefinition
{
    /**
     * @var GridQuestionValidator
     */
    private $validator;

    /**
     * @var GridAnswerValidator
     */
    private $answerValidator;

    /**
     * @var GridQuestionSerializer
     */
    private $serializer;

    /**
     * PairDefinition constructor.
     */
    public function __construct(
        GridQuestionValidator $validator,
        GridAnswerValidator $answerValidator,
        GridQuestionSerializer $serializer
    ) {
        $this->validator = $validator;
        $this->answerValidator = $answerValidator;
        $this->serializer = $serializer;
    }

    /**
     * Gets the grid question mime-type.
     *
     * @return string
     */
    public static function getMimeType()
    {
        return ItemType::GRID;
    }

    /**
     * Gets the grid question entity.
     *
     * @return string
     */
    public static function getEntityClass()
    {
        return '\UJM\ExoBundle\Entity\ItemType\GridQuestion';
    }

    /**
     * Gets the grid question validator.
     *
     * @return GridQuestionValidator
     */
    protected function getQuestionValidator()
    {
        return $this->validator;
    }

    /**
     * Gets the grid answer validator.
     *
     * @return GridAnswerValidator
     */
    protected function getAnswerValidator()
    {
        return $this->answerValidator;
    }

    /**
     * Gets the grid question serializer.
     *
     * @return GridQuestionSerializer
     */
    protected function getQuestionSerializer()
    {
        return $this->serializer;
    }

    /**
     * Used to compute the answer(s) score.
     *
     * @param GridQuestion $question
     * @param array        $answer
     *
     * @return CorrectedAnswer
     */
    public function correctAnswer(AbstractItem $question, $answer)
    {
        $scoreRule = json_decode($question->getQuestion()->getScoreRule(), true);

        if ('fixed' === $scoreRule['type']) {
            return $this->getCorrectAnswerForFixMode($question, $answer);
        } else {
            // 3 sum submode
            switch ($question->getSumMode()) {
              case GridQuestion::SUM_CELL:
                return $this->getCorrectAnswerForSumCellsMode($question, $answer);
              break;
              case GridQuestion::SUM_COLUMN:
                return $this->getCorrectAnswerForColumnSumMode($question, $answer);
              break;
              case GridQuestion::SUM_ROW:
                return $this->getCorrectAnswerForRowSumMode($question, $answer);
              break;
            }
        }
    }

    /**
     * @param GridQuestion $question
     * @param array        $answer
     *
     * @return CorrectedAnswer
     */
    private function getCorrectAnswerForFixMode(AbstractItem $question, $answer)
    {
        $corrected = new CorrectedAnswer();

        if (!is_null($answer)) {
            foreach ($answer as $gridAnswer) {
                $cell = $question->getCell($gridAnswer['cellId']);
                $choice = $cell->getChoice($gridAnswer['text']);

                if (!empty($choice)) {
                    if ($choice->isExpected()) {
                        $corrected->addExpected($choice);
                    } else {
                        $corrected->addUnexpected($choice);
                    }
                } else {
                    // Retrieve the best answer for the cell
                    $corrected->addMissing($this->findCellExpectedAnswer($cell));
                }
            }
        } else {
            $cells = $question->getCells();

            foreach ($cells as $cell) {
                if (0 < count($cell->getChoices())) {
                    $corrected->addMissing($this->findCellExpectedAnswer($cell));
                }
            }
        }

        return $corrected;
    }

    /**
     * @param GridQuestion $question
     * @param array        $answer
     *
     * @return CorrectedAnswer
     */
    private function getCorrectAnswerForSumCellsMode(AbstractItem $question, $answer)
    {
        $corrected = new CorrectedAnswer();

        if (!is_null($answer)) {
            foreach ($answer as $gridAnswer) {
                $cell = $question->getCell($gridAnswer['cellId']);
                $choice = $cell->getChoice($gridAnswer['text']);

                if (!empty($choice)) {
                    if (0 < $choice->getScore()) {
                        $corrected->addExpected($choice);
                    } else {
                        $corrected->addUnexpected($choice);
                    }
                } else {
                    // Retrieve the best answer for the cell
                    $corrected->addMissing($this->findCellExpectedAnswer($cell));
                }
            }
        } else {
            $cells = $question->getCells();

            foreach ($cells as $cell) {
                if ($cell->isInput()) {
                    $corrected->addMissing($this->findCellExpectedAnswer($cell));
                }
            }
        }

        return $corrected;
    }

    /**
     * @param GridQuestion $question
     * @param array        $answer
     *
     * @return CorrectedAnswer
     */
    private function getCorrectAnswerForRowSumMode(AbstractItem $question, $answer)
    {
        $corrected = new CorrectedAnswer();

        if (!is_null($answer)) {
            // correct answers per row
            $nbRows = $question->getRows();
            for ($i = 0; $i < $nbRows; ++$i) {
                // get cells where there is at least 1 expected answer for the current row (none possible)
                $rowCellsUuids = [];

                foreach ($question->getCells() as $cell) {
                    if ($cell->getCoordsY() === $i && $cell->isInput()) {
                        $rowCellsUuids[] = $cell->getUuid();
                    }
                }

                // if any answer is needed in this row
                if (!empty($rowCellsUuids)) {
                    // score will be applied only if all expected answers are there and valid
                    $all = true;

                    foreach ($rowCellsUuids as $expectedCellUuid) {
                        // if $expectedCellUuid found in answers
                        $givenAnwser = array_filter($answer, function ($given) use ($expectedCellUuid) {
                            return $given['cellId'] === $expectedCellUuid;
                        });
                        if (empty($givenAnwser)) {
                            $all = false;
                            break;
                        } else {
                            // check answer
                            $cell = $question->getCell($expectedCellUuid);
                            $currentAnswer = reset($givenAnwser);
                            $choice = $cell->getChoice($currentAnswer['text']);
                            // wrong or empty anwser -> score will not be applied
                            if (empty($choice) || (!empty($choice) && !$choice->isExpected())) {
                                $all = false;
                                break;
                            }
                        }
                    }

                    if ($all) {
                        $scoreToApply = $choice->getScore();
                        $corrected->addExpected(new GenericScore($scoreToApply));
                    } else {
                        $corrected->addPenalty(new GenericPenalty($question->getPenalty()));
                    }
                }
            }
        }

        return $corrected;
    }

    /**
     * @param GridQuestion $question
     * @param array        $answer
     *
     * @return CorrectedAnswer
     */
    private function getCorrectAnswerForColumnSumMode(AbstractItem $question, $answer)
    {
        $corrected = new CorrectedAnswer();

        if (!is_null($answer)) {
            // correct answers per row
            $nbColumns = $question->getColumns();
            for ($i = 0; $i < $nbColumns; ++$i) {
                // get cells where there is at least 1 expected answers for the current column (none possible)
                $colCellsUuids = [];

                foreach ($question->getCells() as $cell) {
                    if ($cell->getCoordsX() === $i && $cell->isInput()) {
                        $colCellsUuids[] = $cell->getUuid();
                    }
                }

                // if any answer is needed in this column
                if (!empty($colCellsUuids)) {
                    // score will be applied only if all expected answers are there and valid
                    $all = true;

                    foreach ($colCellsUuids as $expectedCellUuid) {
                        // if $expectedCellUuid found in answers
                        $givenAnwser = array_filter($answer, function ($given) use ($expectedCellUuid) {
                            return $given['cellId'] === $expectedCellUuid;
                        });
                        if (empty($givenAnwser)) {
                            $all = false;
                            break;
                        } else {
                            $cell = $question->getCell($expectedCellUuid);
                            $currentAnswer = reset($givenAnwser);
                            $choice = $cell->getChoice($currentAnswer['text']);
                            // wrong or empty anwser -> score will not be applied
                            if (empty($choice) || (!empty($choice) && !$choice->isExpected())) {
                                $all = false;
                                break;
                            }
                        }
                    }

                    if ($all) {
                        $scoreToApply = $choice->getScore();
                        $corrected->addExpected(new GenericScore($scoreToApply));
                    } else {
                        $corrected->addPenalty(new GenericPenalty($question->getPenalty()));
                    }
                }
            }
        } else {
            $cells = $question->getCells();

            foreach ($cells as $cell) {
                if ($cell->isInput()) {
                    $corrected->addMissing($this->findCellExpectedAnswer($cell));
                }
            }
        }

        return $corrected;
    }

    /**
     * Used to compute the question total score. Only for sum score type.
     *
     * @param GridQuestion $question
     *
     * @return AnswerPartInterface[]
     */
    public function expectAnswer(AbstractItem $question)
    {
        $expected = [];

        switch ($question->getSumMode()) {
            case GridQuestion::SUM_CELL:
                foreach ($question->getCells()->toArray() as $cell) {
                    if (0 < count($cell->getChoices())) {
                        $expected[] = $this->findCellExpectedAnswer($cell);
                    }
                }
                break;
            case GridQuestion::SUM_COLUMN:
                $nbColumns = $question->getColumns();
                for ($i = 0; $i < $nbColumns; ++$i) {
                    // get cells where there is at least 1 expected answers for the current column (none possible)
                    foreach ($question->getCells() as $cell) {
                        // found a cell in this col with an expected answer
                        if ($cell->getCoordsX() === $i && 0 < count($cell->getChoices())) {
                            // all choices should have the same score
                            $scoreToApply = $cell->getChoices()[0]->getScore();
                            $expected[] = new GenericScore($scoreToApply);
                            break;
                        }
                    }
                }
                break;
            case GridQuestion::SUM_ROW:
                $nbRows = $question->getRows();
                for ($i = 0; $i < $nbRows; ++$i) {
                    // get cells where there is at least 1 expected answers for the current row (none possible)
                    foreach ($question->getCells() as $cell) {
                        // found a cell in this row with an expected answer
                        if ($cell->getCoordsY() === $i && 0 < count($cell->getChoices())) {
                            // all choices should have the same score
                            $scoreToApply = $cell->getChoices()[0]->getScore();
                            $expected[] = new GenericScore($scoreToApply);
                            break;
                        }
                    }
                }
                break;
        }

        return $expected;
    }

    /**
     * @param GridQuestion $question
     *
     * @return AnswerPartInterface[]
     */
    public function allAnswers(AbstractItem $question)
    {
        $answers = [];
        foreach ($question->getCells() as $cell) {
            $answers = array_merge($answers, $cell->getChoices()->toArray());
        }

        return $answers;
    }

    public function getStatistics(AbstractItem $gridQuestion, array $answersData, $total)
    {
        $cells = [];
        $answered = [];
        $nbUnanswered = $total - count($answersData);

        // Create an array with cellId => cellObject for easy search
        $cellsMap = [];
        /** @var Cell $cell */
        foreach ($gridQuestion->getCells() as $cell) {
            $cellsMap[$cell->getUuid()] = $cell;
            $answered[$cell->getUuid()] = 0;
        }

        foreach ($answersData as $answerData) {
            foreach ($answerData as $cellAnswer) {
                if (!empty($cellAnswer['text'])) {
                    $answered[$cellAnswer['cellId']] = isset($answered[$cellAnswer['cellId']]) ?
                        $answered[$cellAnswer['cellId']] + 1 :
                        1;

                    if (!isset($cells[$cellAnswer['cellId']])) {
                        $cells[$cellAnswer['cellId']] = [];
                    }

                    $choice = isset($cellsMap[$cellAnswer['cellId']]) ?
                      $cellsMap[$cellAnswer['cellId']]->getChoice($cellAnswer['text']) :
                      null;

                    if ($choice) {
                        $cells[$cellAnswer['cellId']][$choice->getText()] = isset($cells[$cellAnswer['cellId']][$choice->getText()]) ?
                            $cells[$cellAnswer['cellId']][$choice->getText()] + 1 :
                            1;
                    } else {
                        $cells[$cellAnswer['cellId']]['_others'] = isset($cells[$cellAnswer['cellId']]['_others']) ?
                            $cells[$cellAnswer['cellId']]['_others'] + 1 :
                            1;
                    }
                }
            }
        }
        foreach ($gridQuestion->getCells() as $cell) {
            $cellId = $cell->getUuid();

            if (0 < count($answersData) - $answered[$cellId]) {
                if (!isset($cells[$cellId])) {
                    $cells[$cellId] = [];
                }
                $cells[$cellId]['_unanswered'] = count($answersData) - $answered[$cellId];
            }
        }

        return [
            'cells' => $cells,
            'total' => $total,
            'unanswered' => $nbUnanswered,
        ];
    }

    /**
     * Refreshes cells UUIDs.
     *
     * @param GridQuestion $item
     */
    public function refreshIdentifiers(AbstractItem $item)
    {
        /** @var Cell $cell */
        foreach ($item->getCells() as $cell) {
            $cell->refreshUuid();
        }
    }

    /**
     * @return CellChoice|null
     */
    private function findCellExpectedAnswer(Cell $cell)
    {
        $best = null;
        foreach ($cell->getChoices() as $choice) {
            /** @var CellChoice $choice */
            if (empty($best) || $best->getScore() < $choice->getScore()) {
                $best = $choice;
            }
        }

        return $best;
    }

    public function getCsvTitles(AbstractItem $item)
    {
        return array_map(function (Cell $choice) {
            return 'grid-'.$choice->getUuid();
        }, $item->getCells()->toArray());
    }

    public function getCsvAnswers(AbstractItem $item, Answer $answer)
    {
        $data = json_decode($answer->getData(), true);
        $values = array_map(function ($el) {
            return "[grid-{$el['cellId']}: {$el['text']}]";
        }, $data);
        $compressor = new ArrayCompressor();

        return [$compressor->compress($values)];
    }
}
