<?php

namespace UJM\ExoBundle\Serializer\Item\Type;

use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Entity\ItemType\GridQuestion;
use UJM\ExoBundle\Entity\Misc\Cell;
use UJM\ExoBundle\Entity\Misc\CellChoice;
use UJM\ExoBundle\Library\Options\Transfer;
use UJM\ExoBundle\Library\Serializer\SerializerInterface;
use UJM\ExoBundle\Serializer\Misc\CellChoiceSerializer;

/**
 * @DI\Service("ujm_exo.serializer.question_grid")
 */
class GridQuestionSerializer implements SerializerInterface
{
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
     * @return \stdClass
     */
    public function serialize($gridQuestion, array $options = [])
    {
        $questionData = new \stdClass();
        $questionData->penalty = $gridQuestion->getPenalty();
        $questionData->rows = $gridQuestion->getRows();
        $questionData->cols = $gridQuestion->getColumns();
        $questionData->sumMode = $gridQuestion->getSumMode();
        $questionData->border = $gridQuestion->getGridStyle();
        $questionData->cells = $this->serializeCells($gridQuestion, $options);
        if (in_array(Transfer::INCLUDE_SOLUTIONS, $options)) {
            $questionData->solutions = $this->serializeSolutions($gridQuestion, $options);
        }

        return $questionData;
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
            $cellData = new \stdClass();
            $cellData->id = $cell->getUuid();
            if ($cell->getData()) {
                $cellData->data = $cell->getData();
            }
            $cellData->background = $cell->getBackground();
            $cellData->color = $cell->getColor();
            $cellData->coordinates = $cell->getCoords();
            $cellData->input = $cell->isInput();
            // add a list of choice if needed
            if ($cell->isSelector()) {
                // We want to render a list of choices
                $cellData->choices = array_map(function (CellChoice $choice) use ($cellData) {
                    return $choice->getText();
                }, $cell->getChoices()->toArray());
            } else {
                $cellData->choices = [];
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
                $solutionData = new \stdClass();
                $solutionData->cellId = $cell->getUuid();
                $solutionData->answers = array_map(function (CellChoice $choice) use ($options) {
                    return $this->cellChoiceSerializer->serialize($choice, $options);
                }, $cellChoices);

                $solutions[] = $solutionData;
            }
        }

        return $solutions;
    }

    /**
     * Converts raw data into a Grid question entity.
     *
     * @param \stdClass    $data
     * @param GridQuestion $gridQuestion
     * @param array        $options
     *
     * @return PairQuestion
     */
    public function deserialize($data, $gridQuestion = null, array $options = [])
    {
        if (empty($gridQuestion)) {
            $gridQuestion = new GridQuestion();
        }

        if (!empty($data->penalty) || 0 === $data->penalty) {
            $gridQuestion->setPenalty($data->penalty);
        }

        if (!empty($data->data)) {
            $gridQuestion->setData($data->data);
        }

        $gridQuestion->setRows($data->rows);
        $gridQuestion->setColumns($data->cols);

        if (!empty($data->sumMode)) {
            $gridQuestion->setSumMode($data->sumMode);
        }

        if ($data->border instanceof \stdClass) {
            //during the import, we're an instance of /stdClass otherwise I'm not sure
            //we should remove the array version IMO
            $gridQuestion->setBorderWidth($data->border->width);
            $gridQuestion->setBorderColor($data->border->color);
        } else {
            $gridQuestion->setBorderWidth($data->border['width']);
            $gridQuestion->setBorderColor($data->border['color']);
        }

        // Deserialize cells and solutions
        $this->deserializeCells($gridQuestion, $data->cells, $data->solutions, $options);

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
                if ($entityCell->getUuid() === $cellData->id) {
                    $cell = $entityCell;
                    unset($cellEntities[$entityIndex]);
                    break;
                }
            }

            $cell = $cell ?: new Cell();
            $cell->setUuid($cellData->id);
            $cell->setCoordsX($cellData->coordinates[0]);
            $cell->setCoordsY($cellData->coordinates[1]);
            $cell->setColor($cellData->color);
            $cell->setBackground($cellData->background);

            if (!empty($cellData->data)) {
                $cell->setData($cellData->data);
            }

            $cell->setInput($cellData->input);

            if (!empty($cellData->choices)) {
                $cell->setSelector(true);
            } else {
                $cell->setSelector(false);
            }

            foreach ($solutions as $solution) {
                if ($solution->cellId === $cellData->id) {
                    $this->deserializeCellChoices($cell, $solution->answers, $options);
                    break;
                }
            }

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
