<?php

namespace UJM\ExoBundle\Serializer\Item\Type;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use UJM\ExoBundle\Entity\ItemType\GridQuestion;
use UJM\ExoBundle\Entity\Misc\Cell;
use UJM\ExoBundle\Entity\Misc\CellChoice;
use UJM\ExoBundle\Library\Options\Transfer;
use UJM\ExoBundle\Serializer\Misc\CellChoiceSerializer;

class GridQuestionSerializer
{
    use SerializerTrait;

    /**
     * @var CellChoiceSerializer
     */
    private $cellChoiceSerializer;

    /**
     * GridQuestionSerializer constructor.
     */
    public function __construct(CellChoiceSerializer $cellChoiceSerializer)
    {
        $this->cellChoiceSerializer = $cellChoiceSerializer;
    }

    public function getName()
    {
        return 'exo_question_grid';
    }

    /**
     * Converts a Grid question into a JSON-encodable structure.
     *
     * @return array
     */
    public function serialize(GridQuestion $gridQuestion, array $options = [])
    {
        $serialized = [
            'penalty' => $gridQuestion->getPenalty(),
            'rows' => $gridQuestion->getRows(),
            'cols' => $gridQuestion->getColumns(),
            'sumMode' => $gridQuestion->getSumMode(),
            'border' => $gridQuestion->getGridStyle(),
            'cells' => $this->serializeCells($gridQuestion, $options),
        ];

        if (in_array(Transfer::INCLUDE_SOLUTIONS, $options)) {
            $serialized['solutions'] = $this->serializeSolutions($gridQuestion, $options);
        }

        return $serialized;
    }

    /**
     * @return array
     */
    private function serializeCells(GridQuestion $gridQuestion, array $options = [])
    {
        return array_map(function (Cell $cell) use ($options) {
            $cellData = [
                'id' => $cell->getUuid(),
                'background' => $cell->getBackground(),
                'color' => $cell->getColor(),
                'coordinates' => $cell->getCoords(),
                'input' => $cell->isInput(),
                'random' => $cell->getShuffle(),
            ];
            if ($cell->getData()) {
                $cellData['data'] = $cell->getData();
            }
            // add a list of choice if needed
            if ($cell->isSelector()) {
                // We want to render a list of choices
                $choices = array_map(function (CellChoice $choice) {
                    return $choice->getText();
                }, $cell->getChoices()->toArray());

                if ($cell->getShuffle() && in_array(Transfer::SHUFFLE_ANSWERS, $options)) {
                    shuffle($choices);
                }

                $cellData['choices'] = $choices;
            } else {
                $cellData['choices'] = [];
            }

            return $cellData;
        }, $gridQuestion->getCells()->toArray());
    }

    /**
     * @return array
     */
    private function serializeSolutions(GridQuestion $gridQuestion, array $options = [])
    {
        $solutions = [];

        foreach ($gridQuestion->getCells()->toArray() as $cell) {
            $cellChoices = $cell->getChoices()->toArray();

            if (!empty($cellChoices)) {
                $solutionData = [
                    'cellId' => $cell->getUuid(),
                    'answers' => [],
                ];

                foreach ($cellChoices as $choice) {
                    $solutionData['answers'][] = $this->cellChoiceSerializer->serialize($choice, $options);
                }
                $solutions[] = $solutionData;
            }
        }

        return $solutions;
    }

    /**
     * Converts raw data into a Grid question entity.
     *
     * @param array        $data
     * @param GridQuestion $gridQuestion
     *
     * @return GridQuestion
     */
    public function deserialize($data, GridQuestion $gridQuestion = null, array $options = [])
    {
        if (empty($gridQuestion)) {
            $gridQuestion = new GridQuestion();
        }
        if (!empty($data['penalty']) || 0 === $data['penalty']) {
            $gridQuestion->setPenalty($data['penalty']);
        }
        $this->sipe('data', 'setData', $data, $gridQuestion);
        $this->sipe('rows', 'setRows', $data, $gridQuestion);
        $this->sipe('cols', 'setColumns', $data, $gridQuestion);
        $this->sipe('sumMode', 'setSumMode', $data, $gridQuestion);
        $this->sipe('border.width', 'setBorderWidth', $data, $gridQuestion);
        $this->sipe('border.color', 'setBorderColor', $data, $gridQuestion);

        // Deserialize cells and solutions
        $this->deserializeCells($gridQuestion, $data['cells'], $data['solutions'], $options);

        return $gridQuestion;
    }

    /**
     * Deserializes Question cells.
     */
    private function deserializeCells(GridQuestion $gridQuestion, array $cells, array $solutions, array $options = [])
    {
        $cellEntities = $gridQuestion->getCells()->toArray();

        foreach ($cells as $cellData) {
            $cell = null;

            // Searches for an existing cell entity.
            foreach ($cellEntities as $entityIndex => $entityCell) {
                /* @var Cell $entityCell */
                if ($entityCell->getUuid() === $cellData['id']) {
                    $cell = $entityCell;
                    unset($cellEntities[$entityIndex]);
                    break;
                }
            }

            $cell = $cell ?: new Cell();
            $cell->setUuid($cellData['id']);
            $cell->setCoordsX($cellData['coordinates'][0]);
            $cell->setCoordsY($cellData['coordinates'][1]);
            $cell->setColor($cellData['color']);
            $cell->setBackground($cellData['background']);

            if (!empty($cellData['data'])) {
                $cell->setData($cellData['data']);
            }
            if (!empty($cellData['choices'])) {
                $cell->setSelector(true);
                $cell->setShuffle(!empty($cellData['random']));
            } else {
                $cell->setSelector(false);
                $cell->setShuffle(false);
            }
            $hasSolution = false;

            foreach ($solutions as $solution) {
                if ($solution['cellId'] === $cellData['id']) {
                    $this->deserializeCellChoices($cell, $solution['answers'], $options);
                    $hasSolution = true;
                    break;
                }
            }
            if (!$hasSolution) {
                $this->deserializeCellChoices($cell, [], $options);
            }
            $cell->setInput($hasSolution);

            $gridQuestion->addCell($cell);
        }

        // Remaining cells are no longer in the Question
        foreach ($cellEntities as $cellToRemove) {
            $gridQuestion->removeCell($cellToRemove);
        }
    }

    private function deserializeCellChoices(Cell $cell, array $answers, array $options)
    {
        $updatedChoices = $this->cellChoiceSerializer->deserializeCollection($answers, $cell->getChoices()->toArray(), $options);
        $cell->setChoices($updatedChoices);
    }
}
