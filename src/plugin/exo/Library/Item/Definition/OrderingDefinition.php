<?php

namespace UJM\ExoBundle\Library\Item\Definition;

use UJM\ExoBundle\Entity\Attempt\Answer;
use UJM\ExoBundle\Entity\ItemType\AbstractItem;
use UJM\ExoBundle\Entity\ItemType\OrderingQuestion;
use UJM\ExoBundle\Entity\Misc\OrderingItem;
use UJM\ExoBundle\Library\Attempt\CorrectedAnswer;
use UJM\ExoBundle\Library\Attempt\GenericPenalty;
use UJM\ExoBundle\Library\Csv\ArrayCompressor;
use UJM\ExoBundle\Library\Item\ItemType;
use UJM\ExoBundle\Serializer\Item\Type\OrderingQuestionSerializer;
use UJM\ExoBundle\Validator\JsonSchema\Attempt\AnswerData\OrderingAnswerValidator;
use UJM\ExoBundle\Validator\JsonSchema\Item\Type\OrderingQuestionValidator;

/**
 * Ordering question definition.
 */
class OrderingDefinition extends AbstractDefinition
{
    public function __construct(
        private readonly OrderingQuestionValidator $validator,
        private readonly OrderingAnswerValidator $answerValidator,
        private readonly OrderingQuestionSerializer $serializer
    ) {
    }

    public static function getMimeType(): string
    {
        return ItemType::ORDERING;
    }

    public static function getEntityClass(): string
    {
        return OrderingQuestion::class;
    }

    protected function getQuestionValidator(): OrderingQuestionValidator
    {
        return $this->validator;
    }

    protected function getAnswerValidator(): OrderingAnswerValidator
    {
        return $this->answerValidator;
    }

    protected function getQuestionSerializer(): OrderingQuestionSerializer
    {
        return $this->serializer;
    }

    /**
     * @param OrderingQuestion $question
     */
    public function correctAnswer(AbstractItem $question, mixed $answer = []): CorrectedAnswer
    {
        $corrected = new CorrectedAnswer();

        if (is_array($answer)) {
            foreach ($answer as $givenAnswer) {
                $item = $question->getItem($givenAnswer['itemId']);
                if (!empty($item->getPosition()) && $item->getPosition() === $givenAnswer['position']) {
                    $corrected->addExpected($item);
                } else {
                    $penalty = new GenericPenalty($question->getPenalty());
                    $corrected->addPenalty($penalty);
                }
            }
        }

        return $corrected;
    }

    /**
     * @param OrderingQuestion $question
     */
    public function expectAnswer(AbstractItem $question): array
    {
        return array_filter($question->getItems()->toArray(), function (OrderingItem $item) {
            return !empty($item->getPosition());
        });
    }

    /**
     * @param OrderingQuestion $question
     */
    public function allAnswers(AbstractItem $question): array
    {
        return $question->getItems()->toArray();
    }

    /**
     * @param OrderingQuestion $question
     */
    public function getStatistics(AbstractItem $question, array $answersData, int $total): array
    {
        $orders = [];
        $unused = [];
        $unusedItems = [];

        foreach ($question->getItems()->toArray() as $item) {
            $unusedItems[$item->getUuid()] = true;
        }
        foreach ($answersData as $answerData) {
            $unusedTemp = array_merge($unusedItems);
            $orderingKey = '';

            usort($answerData, function ($a, $b) {
                return $a['position'] - $b['position'];
            });

            foreach ($answerData as $orderingAnswer) {
                $orderingKey .= $orderingAnswer['itemId'];
                $unusedTemp[$orderingAnswer['itemId']] = false;
            }
            if (!isset($orders[$orderingKey])) {
                $orders[$orderingKey] = [
                    'data' => $answerData,
                    'count' => 0,
                ];
            }
            ++$orders[$orderingKey]['count'];

            foreach ($unusedTemp as $itemId => $value) {
                if ($value) {
                    $unused[$itemId] = isset($unused[$itemId]) ? $unused[$itemId] + 1 : 1;
                }
            }
        }

        return [
            'orders' => $orders,
            'unused' => $unused,
            'total' => $total,
            'unanswered' => $total - count($answersData),
        ];
    }

    /**
     * Refreshes item UUIDs.
     *
     * @param OrderingQuestion $question
     */
    public function refreshIdentifiers(AbstractItem $question): void
    {
        foreach ($question->getItems() as $orderingItem) {
            $orderingItem->refreshUuid();
        }
    }

    /**
     * @param OrderingQuestion $question
     */
    public function getCsvAnswers(AbstractItem $question, Answer $answer): array
    {
        $data = json_decode($answer->getData(), true);
        $items = $question->getItems();
        $answers = [];

        foreach ($data as $el) {
            foreach ($items as $item) {
                if ($item->getUuid() === $el['itemId']) {
                    $answers[] = $item->getData();
                }
            }
        }

        $compressor = new ArrayCompressor();

        return [$compressor->compress($answers)];
    }
}
