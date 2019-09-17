<?php

namespace UJM\ExoBundle\Serializer\Item\Type;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use UJM\ExoBundle\Entity\ItemType\PairQuestion;
use UJM\ExoBundle\Entity\Misc\GridItem;
use UJM\ExoBundle\Entity\Misc\GridOdd;
use UJM\ExoBundle\Entity\Misc\GridRow;
use UJM\ExoBundle\Library\Options\Transfer;
use UJM\ExoBundle\Serializer\Content\ContentSerializer;

class PairQuestionSerializer
{
    use SerializerTrait;

    /**
     * @var ContentSerializer
     */
    private $contentSerializer;

    /**
     * PairQuestionSerializer constructor.
     *
     * @param ContentSerializer $contentSerializer
     */
    public function __construct(ContentSerializer $contentSerializer)
    {
        $this->contentSerializer = $contentSerializer;
    }

    /**
     * Converts a Match question into a JSON-encodable structure.
     *
     * @param PairQuestion $pairQuestion
     * @param array        $options
     *
     * @return array
     */
    public function serialize(PairQuestion $pairQuestion, array $options = [])
    {
        $serialized = [
            'random' => $pairQuestion->getShuffle(),
            'penalty' => $pairQuestion->getPenalty(),
            'rows' => $pairQuestion->getRows()->filter(function (GridRow $row) {
                return 0 < $row->getScore();
            })->count(),  // The grid only contains expected answers
        ];

        $items = $this->serializeItems($pairQuestion, $options);

        if (in_array(Transfer::SHUFFLE_ANSWERS, $options)) {
            shuffle($items); // shuffle the pool of items
        }

        $serialized['items'] = $items;

        if (in_array(Transfer::INCLUDE_SOLUTIONS, $options)) {
            $serialized['solutions'] = $this->serializeSolutions($pairQuestion);
        }

        return $serialized;
    }

    private function serializeItems(PairQuestion $pairQuestion, array $options = [])
    {
        $usedCoordinates = [];

        return array_values(array_map(function (GridItem $item) use ($pairQuestion, $options, &$usedCoordinates) {
            $itemData = $this->contentSerializer->serialize($item, $options);
            $itemData['id'] = $item->getUuid();

            if ($item->getCoords()) {
                if (in_array(Transfer::SHUFFLE_ANSWERS, $options) && $pairQuestion->getShuffle()) {
                    // Shuffle pinned items in rows
                    $itemData['coordinates'] = $this->generateNewCoords($item->getCoords(), $pairQuestion->getRows()->count(), $usedCoordinates);
                } else {
                    // Just get the coordinates defined by the creator
                    $itemData['coordinates'] = $item->getCoords();
                }
            }

            return $itemData;
        }, $pairQuestion->getItems()->toArray()));
    }

    private function generateNewCoords($coords, $rows, array &$usedCoords)
    {
        do {
            // Generate new position
            $newY = rand(0, $rows - 1);
            $newCoords = [$coords[0], $newY];
        } while (in_array($newCoords, $usedCoords));

        $usedCoords[] = $newCoords;

        return $newCoords;
    }

    /**
     * Converts raw data into a Pair question entity.
     *
     * @param array        $data
     * @param PairQuestion $pairQuestion
     * @param array        $options
     *
     * @return PairQuestion
     */
    public function deserialize($data, PairQuestion $pairQuestion = null, array $options = [])
    {
        if (empty($pairQuestion)) {
            $pairQuestion = new PairQuestion();
        }

        if (!empty($data['penalty']) || 0 === $data['penalty']) {
            $pairQuestion->setPenalty($data['penalty']);
        }
        $this->sipe('random', 'setShuffle', $data, $pairQuestion);

        $this->deserializeItems($pairQuestion, $data['items'], $options);
        $this->deserializeSolutions($pairQuestion, $data['solutions']);

        return $pairQuestion;
    }

    private function deserializeItems(PairQuestion $pairQuestion, array $items, array $options = [])
    {
        $itemEntities = $pairQuestion->getItems()->toArray();

        foreach ($items as $itemData) {
            $item = null;

            // Searches for an existing choice entity.
            foreach ($itemEntities as $entityIndex => $entityItem) {
                /** @var GridItem $entityItem */
                if ($entityItem->getUuid() === $itemData['id']) {
                    $item = $entityItem;
                    unset($itemEntities[$entityIndex]);
                    break;
                }
            }

            $item = $item ?: new GridItem();
            $item->setUuid($itemData['id']);

            if (isset($itemData['coordinates'])) {
                $item->setCoordsX($itemData['coordinates'][0]);
                $item->setCoordsY($itemData['coordinates'][1]);
            } else {
                // explicitly set coordinates to NULL in case of previous values
                $item->setCoordsX(null);
                $item->setCoordsY(null);
            }

            // Deserialize choice content
            $item = $this->contentSerializer->deserialize($itemData, $item, $options);

            $pairQuestion->addItem($item);
        }

        // Remaining choices are no longer in the Question
        foreach ($itemEntities as $itemToRemove) {
            $pairQuestion->removeItem($itemToRemove);
        }
    }

    private function deserializeSolutions(PairQuestion $pairQuestion, array $solutions)
    {
        $rowEntities = $pairQuestion->getRows()->toArray();
        $oddEntities = $pairQuestion->getOddItems()->toArray();

        foreach ($solutions as $solution) {
            $solutionEntity = null;
            if (1 === count($solution['itemIds'])) {
                // This is an odd
                $solutionEntity = $this->deserializeOddItem($pairQuestion, $solution, $oddEntities);
            } else {
                // This is a row
                $solutionEntity = $this->deserializeRow($pairQuestion, $solution, $rowEntities);
            }

            // Common parts between odd and row
            $solutionEntity->setScore($solution['score']);
            if (isset($solution['feedback'])) {
                $solutionEntity->setFeedback($solution['feedback']);
            }
        }

        // Remaining associations are no longer in the Question
        foreach ($rowEntities as $rowToRemove) {
            $pairQuestion->removeRow($rowToRemove);
        }

        foreach ($oddEntities as $oddToRemove) {
            $pairQuestion->removeOddItem($oddToRemove);
        }
    }

    private function deserializeRow(PairQuestion $pairQuestion, array $rowData, array &$existingRows)
    {
        $row = null;
        // Retrieve existing row to update
        foreach ($existingRows as $entityIndex => $entityRow) {
            /* @var GridRow $entityRow */
            if ($rowData['itemIds'] === $entityRow->getItemIds()) {
                // This is the only way we can retrieve an existing row because
                // it has no unique identifier in the transfer schema
                // In any other case a new one is created
                $row = $entityRow;

                unset($existingRows[$entityIndex]);
                break;
            }
        }

        if (empty($row)) {
            // New row
            $row = new GridRow();
        }

        if (isset($rowData['ordered'])) {
            $row->setOrdered($rowData['ordered']);
        }

        foreach ($rowData['itemIds'] as $index => $itemId) {
            if ($pairQuestion->getItem($itemId)) {
                $row->addItem($pairQuestion->getItem($itemId), $index);
            }
        }

        $pairQuestion->addRow($row);

        return $row;
    }

    private function deserializeOddItem(PairQuestion $pairQuestion, array $oddItemData, array &$existingOddItems)
    {
        $oddItem = null;
        // Retrieve an existing odd to update
        foreach ($existingOddItems as $entityIndex => $entityOdd) {
            /* @var GridOdd $entityOdd */
            if ($entityOdd->getItem()->getUuid() === $oddItemData['itemIds'][0]) {
                $oddItem = $entityOdd;
                unset($existingOddItems[$entityIndex]);
                break;
            }
        }

        if (empty($oddItem)) {
            // New odd
            $oddItem = new GridOdd();
        }

        $oddItem->setItem($pairQuestion->getItem($oddItemData['itemIds'][0]));
        $pairQuestion->addOddItem($oddItem);

        return $oddItem;
    }

    /**
     * @param PairQuestion $pairQuestion
     *
     * @return array
     */
    private function serializeSolutions(PairQuestion $pairQuestion)
    {
        // Merge rows and odd items in one array
        return array_merge(
            // Rows
            array_map(function (GridRow $row) {
                $solution = [
                    'ordered' => $row->isOrdered(),
                    'itemIds' => 0 < count($row->getItems()) ?
                        array_map(function (GridItem $item) {
                            return $item->getUuid();
                        }, $row->getItems()) :
                        [-1, -1],
                    'score' => $row->getScore(),
                ];

                if ($row->getFeedback()) {
                    $solution['feedback'] = $row->getFeedback();
                }

                return $solution;
            }, $pairQuestion->getRows()->toArray()),
            // Odd items
            array_map(function (GridOdd $odd) {
                $solution = [
                    'itemIds' => [$odd->getItem()->getUuid()],
                    'score' => $odd->getScore(),
                ];

                if ($odd->getFeedback()) {
                    $solution['feedback'] = $odd->getFeedback();
                }

                return $solution;
            }, $pairQuestion->getOddItems()->toArray())
        );
    }
}
