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
    public function __construct(
        private readonly GridQuestionValidator $validator,
        private readonly GridAnswerValidator $answerValidator,
        private readonly GridQuestionSerializer $serializer
    ) {
    }

    public static function getMimeType(): string
    {
        return ItemType::GRID;
    }

    public static function getEntityClass(): string
    {
        return GridQuestion::class;
    }

    protected function getQuestionValidator(): GridQuestionValidator
    {
        return $this->validator;
    }

    protected function getAnswerValidator(): GridAnswerValidator
    {
        return $this->answerValidator;
    }

    protected function getQuestionSerializer(): GridQuestionSerializer
    {
        return $this->serializer;
    }

    /**
     * @param GridQuestion $question
     */
    public function correctAnswer(AbstractItem $question, mixed $answer): CorrectedAnswer
    {
        $scoreRule = json_decode($question->getQuestion()->getScoreRule(), true);

        if ('fixed' !== $scoreRule['type']) {
            switch ($question->getSumMode()) {
                case GridQuestion::SUM_CELL:
                    return $this->getCorrectAnswerForSumCellsMode($question, $answer);
                case GridQuestion::SUM_COLUMN:
                    return $this->getCorrectAnswerForColumnSumMode($question, $answer);
                case GridQuestion::SUM_ROW:
                    return $this->getCorrectAnswerForRowSumMode($question, $answer);
            }
        }

        return $this->getCorrectAnswerForFixMode($question, $answer);
    }

    private function getCorrectAnswerForFixMode(GridQuestion $question, ?array $answer): CorrectedAnswer
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

    private function getCorrectAnswerForSumCellsMode(GridQuestion $question, ?array $answer): CorrectedAnswer
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

    private function getCorrectAnswerForRowSumMode(GridQuestion $question, ?array $answer): CorrectedAnswer
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

    private function getCorrectAnswerForColumnSumMode(GridQuestion $question, ?array $answer): CorrectedAnswer
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
     */
    public function expectAnswer(AbstractItem $question): array
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
     */
    public function allAnswers(AbstractItem $question): array
    {
        $answers = [];
        foreach ($question->getCells() as $cell) {
            $answers = array_merge($answers, $cell->getChoices()->toArray());
        }

        return $answers;
    }

    /**
     * @param GridQuestion $question
     */
    public function getStatistics(AbstractItem $question, array $answersData, int $total): array
    {
        $cells = [];
        $answered = [];
        $nbUnanswered = $total - count($answersData);

        // Create an array with cellId => cellObject for easy search
        $cellsMap = [];
        /** @var Cell $cell */
        foreach ($question->getCells() as $cell) {
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
        foreach ($question->getCells() as $cell) {
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
     * @param GridQuestion $question
     */
    public function refreshIdentifiers(AbstractItem $question): void
    {
        /** @var Cell $cell */
        foreach ($question->getCells() as $cell) {
            $cell->refreshUuid();
        }
    }

    private function findCellExpectedAnswer(Cell $cell): ?CellChoice
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

    /**
     * @param GridQuestion $question
     */
    public function getCsvTitles(AbstractItem $question): array
    {
        return array_map(function (Cell $choice) {
            return 'grid-'.$choice->getUuid();
        }, $question->getCells()->toArray());
    }

    /**
     * @param GridQuestion $question
     */
    public function getCsvAnswers(AbstractItem $question, Answer $answer): array
    {
        $data = json_decode($answer->getData(), true);
        $values = array_map(function ($el) {
            return "[grid-{$el['cellId']}: {$el['text']}]";
        }, $data);
        $compressor = new ArrayCompressor();

        return [$compressor->compress($values)];
    }
}
