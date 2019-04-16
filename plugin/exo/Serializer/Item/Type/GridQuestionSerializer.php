<?php

namespace UJM\ExoBundle\Serializer\Item\Type;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Entity\ItemType\GridQuestion;
use UJM\ExoBundle\Entity\Misc\Cell;
use UJM\ExoBundle\Entity\Misc\CellChoice;
use UJM\ExoBundle\Library\Options\Transfer;
use UJM\ExoBundle\Serializer\Misc\CellChoiceSerializer;

/**
 * @DI\Service("ujm_exo.serializer.question_grid")
 * @DI\Tag("claroline.serializer")
 */
class GridQuestionSerializer
{
    use SerializerTrait;

    /**
     * @var CellChoiceSerializer
     */
    private $cellChoiceSerializer;

    /**
     * GridQuestionSerializer constructor.
     *
     * @param CellChoiceSerializer $cellChoiceSerializer
     *
     * @DI\InjectParams({
     *     "cellChoiceSerializer" = @DI\Inject("ujm_exo.serializer.cell_choice")
     * })
     */
    public function __construct(CellChoiceSerializer $cellChoiceSerializer)
    {
        $this->cellChoiceSerializer = $cellChoiceSerializer;
    }

    /**
     * Converts a Grid question into a JSON-encodable structure.
     *
     * @param GridQuestion $gridQuestion
     * @param array        $options
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
     * @param GridQuestion $gridQuestion
     * @param array        $options
     *
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
            ];
            if ($cell->getData()) {
                $cellData['data'] = $cell->getData();
            }
            // add a list of choice if needed
            if ($cell->isSelector()) {
                // We want to render a list of choices
                $cellData['choices'] = array_map(function (CellChoice $choice) use ($cellData) {
                    return $choice->getText();
                }, $cell->getChoices()->toArray());
            } else {
                $cellData['choices'] = [];
            }

            return $cellData;
        }, $gridQuestion->getCells()->toArray());
    }

    /**
     * @param GridQuestion $gridQuestion
     * @param array        $options
     *
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
     * @param array        $options
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
     *
     * @param GridQuestion $gridQuestion
     * @param array        $cells
     * @param array        $solutions
     * @param array        $options
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
            } else {
                $cell->setSelector(false);
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
