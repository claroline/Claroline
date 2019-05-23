<?php

namespace UJM\ExoBundle\Library\Item\Definition;

use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Entity\Attempt\Answer;
use UJM\ExoBundle\Entity\ItemType\AbstractItem;
use UJM\ExoBundle\Entity\ItemType\OrderingQuestion;
use UJM\ExoBundle\Entity\Misc\OrderingItem;
use UJM\ExoBundle\Library\Attempt\AnswerPartInterface;
use UJM\ExoBundle\Library\Attempt\CorrectedAnswer;
use UJM\ExoBundle\Library\Attempt\GenericPenalty;
use UJM\ExoBundle\Library\Csv\ArrayCompressor;
use UJM\ExoBundle\Library\Item\ItemType;
use UJM\ExoBundle\Serializer\Item\Type\OrderingQuestionSerializer;
use UJM\ExoBundle\Validator\JsonSchema\Attempt\AnswerData\OrderingAnswerValidator;
use UJM\ExoBundle\Validator\JsonSchema\Item\Type\OrderingQuestionValidator;

/**
 * Ordering question definition.
 *
 * @DI\Service("ujm_exo.definition.question_ordering")
 * @DI\Tag("ujm_exo.definition.item")
 */
class OrderingDefinition extends AbstractDefinition
{
    /**
     * @var OrderingQuestionValidator
     */
    private $validator;

    /**
     * @var OrderingAnswerValidator
     */
    private $answerValidator;

    /**
     * @var OrderingQuestionSerializer
     */
    private $serializer;

    /**
     * OrderingDefinition constructor.
     *
     * @param OrderingQuestionValidator  $validator
     * @param OrderingAnswerValidator    $answerValidator
     * @param OrderingQuestionSerializer $serializer
     *
     * @DI\InjectParams({
     *     "validator"       = @DI\Inject("ujm_exo.validator.question_ordering"),
     *     "answerValidator" = @DI\Inject("ujm_exo.validator.answer_ordering"),
     *     "serializer"      = @DI\Inject("ujm_exo.serializer.question_ordering")
     * })
     */
    public function __construct(
        OrderingQuestionValidator $validator,
        OrderingAnswerValidator $answerValidator,
        OrderingQuestionSerializer $serializer
    ) {
        $this->validator = $validator;
        $this->answerValidator = $answerValidator;
        $this->serializer = $serializer;
    }

    /**
     * Gets the choice question mime-type.
     *
     * @return string
     */
    public static function getMimeType()
    {
        return ItemType::ORDERING;
    }

    /**
     * Gets the choice question entity.
     *
     * @return string
     */
    public static function getEntityClass()
    {
        return '\UJM\ExoBundle\Entity\ItemType\OrderingQuestion';
    }

    /**
     * Gets the ordering question validator.
     *
     * @return OrderingQuestionValidator
     */
    protected function getQuestionValidator()
    {
        return $this->validator;
    }

    /**
     * Gets the ordering answer validator.
     *
     * @return OrderingAnswerValidator
     */
    protected function getAnswerValidator()
    {
        return $this->answerValidator;
    }

    /**
     * Gets the ordering question serializer.
     *
     * @return OrderingQuestionSerializer
     */
    protected function getQuestionSerializer()
    {
        return $this->serializer;
    }

    /**
     * @param OrderingQuestion $question
     * @param $answer
     *
     * @return CorrectedAnswer
     */
    public function correctAnswer(AbstractItem $question, $answer = [])
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
     *
     * @return AnswerPartInterface[]
     */
    public function expectAnswer(AbstractItem $question)
    {
        return array_filter($question->getItems()->toArray(), function (OrderingItem $item) {
            return !empty($item->getPosition());
        });
    }

    /**
     * @param OrderingQuestion $question
     *
     * @return AnswerPartInterface[]
     */
    public function allAnswers(AbstractItem $question)
    {
        return $question->getItems()->toArray();
    }

    public function getStatistics(AbstractItem $question, array $answersData, $total)
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
     * @param OrderingQuestion $item
     */
    public function refreshIdentifiers(AbstractItem $item)
    {
        /** @var OrderingItem $item */
        foreach ($item->getItems() as $orderingItem) {
            $orderingItem->refreshUuid();
        }
    }

    public function getCsvAnswers(AbstractItem $item, Answer $answer)
    {
        $data = json_decode($answer->getData(), true);
        $items = $item->getItems();
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
