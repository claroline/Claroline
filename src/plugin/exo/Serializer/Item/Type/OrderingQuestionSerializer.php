<?php

namespace UJM\ExoBundle\Serializer\Item\Type;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use UJM\ExoBundle\Entity\ItemType\OrderingQuestion;
use UJM\ExoBundle\Entity\Misc\OrderingItem;
use UJM\ExoBundle\Library\Options\Transfer;
use UJM\ExoBundle\Serializer\Content\ContentSerializer;

class OrderingQuestionSerializer
{
    use SerializerTrait;

    public function __construct(
        private readonly ContentSerializer $contentSerializer
    ) {
    }

    public function getName(): string
    {
        return 'exo_question_ordering';
    }

    public function serialize(OrderingQuestion $question, array $options = []): array
    {
        $serialized = [
            'mode' => $question->getMode(),
            'direction' => $question->getDirection(),
            'penalty' => $question->getPenalty(),
        ];

        // Serializes items
        $items = $this->serializeItems($question, $options);

        // shuffle items only in player
        if (in_array(Transfer::SHUFFLE_ANSWERS, $options)) {
            shuffle($items);
        }
        $serialized['items'] = $items;

        if (in_array(Transfer::INCLUDE_SOLUTIONS, $options)) {
            $serialized['solutions'] = $this->serializeSolutions($question);
        }

        return $serialized;
    }

    private function serializeItems(OrderingQuestion $question, array $options = []): array
    {
        return array_map(function (OrderingItem $item) use ($options) {
            $itemData = $this->contentSerializer->serialize($item, $options);
            $itemData['id'] = $item->getUuid();

            return $itemData;
        }, $question->getItems()->toArray());
    }

    private function serializeSolutions(OrderingQuestion $question): array
    {
        return array_map(function (OrderingItem $item) {
            $solutionData = [
                'itemId' => $item->getUuid(),
                'score' => $item->getScore(),
            ];

            if ($item->getFeedback()) {
                $solutionData['feedback'] = $item->getFeedback();
            }

            if ($item->getPosition()) {
                $solutionData['position'] = $item->getPosition();
            }

            return $solutionData;
        }, $question->getItems()->toArray());
    }

    public function deserialize(array $data, ?OrderingQuestion $question = null, array $options = []): OrderingQuestion
    {
        if (empty($question)) {
            $question = new OrderingQuestion();
        }

        if (!empty($data['penalty']) || 0 === $data['penalty']) {
            $question->setPenalty($data['penalty']);
        }

        $this->sipe('direction', 'setDirection', $data, $question);
        $this->sipe('mode', 'setMode', $data, $question);

        $this->deserializeItems($question, $data['items'], $data['solutions'], $options);

        return $question;
    }

    private function deserializeItems(OrderingQuestion $question, array $items, array $solutions, array $options = []): void
    {
        $itemEntities = $question->getItems()->toArray();

        foreach ($items as $itemData) {
            $item = null;

            // Searches for an existing item entity.
            foreach ($itemEntities as $entityIndex => $entityItem) {
                /** @var OrderingItem $entityItem */
                if ($entityItem->getUuid() === $itemData['id']) {
                    $item = $entityItem;
                    unset($itemEntities[$entityIndex]);
                    break;
                }
            }

            $item = $item ?: new OrderingItem();
            $item->setUuid($itemData['id']);

            // Deserialize item content
            $item = $this->contentSerializer->deserialize($itemData, $item, $options);

            // Set item score feedback and order
            foreach ($solutions as $solution) {
                if ($solution['itemId'] === $itemData['id']) {
                    $item->setScore($solution['score']);

                    if (isset($solution['feedback'])) {
                        $item->setFeedback($solution['feedback']);
                    }

                    if (isset($solution['position'])) {
                        $item->setPosition($solution['position']);
                    }
                    break;
                }
            }

            $question->addItem($item);
        }

        // Remaining items are no longer in the Question
        foreach ($itemEntities as $itemToRemove) {
            $question->removeItem($itemToRemove);
        }
    }
}
