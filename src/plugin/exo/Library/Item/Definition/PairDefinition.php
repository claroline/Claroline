<?php

namespace UJM\ExoBundle\Library\Item\Definition;

use UJM\ExoBundle\Entity\Attempt\Answer;
use UJM\ExoBundle\Entity\ItemType\AbstractItem;
use UJM\ExoBundle\Entity\ItemType\PairQuestion;
use UJM\ExoBundle\Entity\Misc\GridItem;
use UJM\ExoBundle\Entity\Misc\GridRow;
use UJM\ExoBundle\Library\Attempt\CorrectedAnswer;
use UJM\ExoBundle\Library\Attempt\GenericPenalty;
use UJM\ExoBundle\Library\Csv\ArrayCompressor;
use UJM\ExoBundle\Library\Item\ItemType;
use UJM\ExoBundle\Serializer\Item\Type\PairQuestionSerializer;
use UJM\ExoBundle\Validator\JsonSchema\Attempt\AnswerData\PairAnswerValidator;
use UJM\ExoBundle\Validator\JsonSchema\Item\Type\PairQuestionValidator;

/**
 * Pair question definition.
 */
class PairDefinition extends AbstractDefinition
{
    public function __construct(
        private readonly PairQuestionValidator $validator,
        private readonly PairAnswerValidator $answerValidator,
        private readonly PairQuestionSerializer $serializer
    ) {
    }

    public static function getMimeType(): string
    {
        return ItemType::PAIR;
    }

    public static function getEntityClass(): string
    {
        return PairQuestion::class;
    }

    protected function getQuestionValidator(): PairQuestionValidator
    {
        return $this->validator;
    }

    protected function getAnswerValidator(): PairAnswerValidator
    {
        return $this->answerValidator;
    }

    protected function getQuestionSerializer(): PairQuestionSerializer
    {
        return $this->serializer;
    }

    /**
     * @param PairQuestion $question
     */
    public function correctAnswer(AbstractItem $question, mixed $answer): CorrectedAnswer
    {
        $corrected = new CorrectedAnswer();
        $rows = $question->getRows()->toArray();

        if (!is_null($answer)) {
            foreach ($answer as $answerRow) {
                $hasOdd = false;
                // Search for odd items
                foreach ($answerRow as $answerItem) {
                    $odd = $question->getOddItem($answerItem);

                    if (!empty($odd)) {
                        $corrected->addUnexpected($odd);
                        $hasOdd = true;
                    }
                }

                if (!$hasOdd) {
                    // Search for a defined row for the user answer
                    $row = $this->findRowByAnswer($answerRow, $rows);
                    if (!empty($row)) {
                        // Row found
                        if (0 < $row->getScore()) {
                            $corrected->addExpected($row);
                        } else {
                            $corrected->addUnexpected($row);
                        }
                    } else {
                        // user answer is not a defined one, apply default penalty if exist
                        if ($question->getPenalty()) {
                            $corrected->addPenalty(new GenericPenalty($question->getPenalty()));
                        }
                    }
                }
            }
        }

        if (!empty($rows)) {
            // There are defined rows that have not been found
            foreach ($rows as $row) {
                /** @var GridRow $row */
                if (0 < $row->getScore()) {
                    $corrected->addMissing($row);
                }
            }
        }

        return $corrected;
    }

    /**
     * @param PairQuestion $question
     */
    public function expectAnswer(AbstractItem $question): array
    {
        return array_filter($question->getRows()->toArray(), function (GridRow $row) {
            return 0 < $row->getScore();
        });
    }

    /**
     * @param PairQuestion $question
     */
    public function allAnswers(AbstractItem $question): array
    {
        return array_merge($question->getRows()->toArray(), $question->getOddItems()->toArray());
    }

    /**
     * @param PairQuestion $question
     */
    public function getStatistics(AbstractItem $question, array $answersData, int $total): array
    {
        $paired = [];
        $unpaired = [];
        $unusedItems = [];
        $valid = [];

        foreach ($question->getItems()->toArray() as $item) {
            $unusedItems[$item->getUuid()] = true;
        }
        // Initialize acceptable pairs
        foreach ($question->getRows()->toArray() as $row) {
            $rowItems = $row->getItems();

            if (2 === count($rowItems)) {
                $item0Id = $rowItems[0]->getUuid();
                $item1Id = $rowItems[1]->getUuid();

                if (!isset($valid[$item0Id])) {
                    $valid[$item0Id] = [];
                }
                $valid[$item0Id][$item1Id] = true;

                if ($row->isOrdered()) {
                    if (!isset($valid[$item1Id])) {
                        $valid[$item1Id] = [];
                    }
                    $valid[$item1Id][$item0Id] = true;
                }
            }
        }
        // Build remaining acceptable pairs to group inverse pairs together
        foreach ($question->getItems()->toArray() as $i1) {
            foreach ($question->getItems()->toArray() as $i2) {
                if ((!isset($valid[$i1->getUuid()]) || !isset($valid[$i1->getUuid()][$i2->getUuid()]))
                    && (!isset($valid[$i2->getUuid()]) || !isset($valid[$i2->getUuid()][$i1->getUuid()]))
                ) {
                    if (!isset($valid[$i1->getUuid()])) {
                        $valid[$i1->getUuid()] = [];
                    }
                    $valid[$i1->getUuid()][$i2->getUuid()] = true;
                }
            }
        }
        foreach ($answersData as $answerData) {
            $unusedTemp = array_merge($unusedItems);

            foreach ($answerData as $pair) {
                $first = isset($valid[$pair[0]]) && isset($valid[$pair[0]][$pair[1]]) ? $pair[0] : $pair[1];
                $second = isset($valid[$pair[0]]) && isset($valid[$pair[0]][$pair[1]]) ? $pair[1] : $pair[0];

                if (!isset($paired[$first])) {
                    $paired[$first] = [];
                }
                $paired[$first][$second] = isset($paired[$first][$second]) ? $paired[$first][$second] + 1 : 1;
                $unusedTemp[$first] = false;
                $unusedTemp[$second] = false;
            }
            foreach ($unusedTemp as $itemId => $value) {
                if ($value) {
                    $unpaired[$itemId] = isset($unpaired[$itemId]) ? $unpaired[$itemId] + 1 : 1;
                }
            }
        }

        return [
            'paired' => $paired,
            'unpaired' => $unpaired,
            'total' => $total,
            'unanswered' => $total - count($answersData),
        ];
    }

    /**
     * Refreshes items UUIDs.
     *
     * @param PairQuestion $question
     */
    public function refreshIdentifiers(AbstractItem $question): void
    {
        /** @var GridItem $pairItem */
        foreach ($question->getItems() as $pairItem) {
            $pairItem->refreshUuid();
        }
    }

    private function findRowByAnswer(array $items, array &$rows): ?GridRow
    {
        $found = null;

        /** @var GridRow $row */
        foreach ($rows as $index => $row) {
            if ($row->isOrdered()) {
                // answers must be in the correct order
                if ($row->getItemIds() === $items) {
                    $found = $row;
                    unset($rows[$index]);
                    break;
                }
            } else {
                $match = 0;

                foreach ($items as $item) {
                    if ($row->getItem($item)) {
                        ++$match;
                    }
                }

                if ($match === count($row->getItems())) {
                    // All items of the row must be found
                    $found = $row;
                    unset($rows[$index]);
                    break;
                }
            }
        }

        return $found;
    }

    /**
     * @param PairQuestion $question
     */
    public function getCsvAnswers(AbstractItem $question, Answer $answer): array
    {
        $data = json_decode($answer->getData(), true);
        $items = $question->getItems();
        $answers = [];

        foreach ($data as $pair) {
            $answerPair = '[';

            foreach ($items as $el) {
                if ($el->getUuid() === $pair[0]) {
                    $answerPair .= $el->getData();
                }
            }

            $answerPair .= ';';

            foreach ($items as $el) {
                if ($el->getUuid() === $pair[1]) {
                    $answerPair .= $el->getData();
                }
            }

            $answerPair .= ']';
            $answers[] = $answerPair;
        }

        $compressor = new ArrayCompressor();

        return [$compressor->compress($answers)];
    }
}
