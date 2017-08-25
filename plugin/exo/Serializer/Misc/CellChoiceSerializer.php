<?php

namespace UJM\ExoBundle\Serializer\Misc;

use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Entity\Misc\CellChoice;
use UJM\ExoBundle\Library\Serializer\SerializerInterface;

/**
 * Serializer for CellChoice data.
 *
 * @DI\Service("ujm_exo.serializer.cell_choice")
 */
class CellChoiceSerializer implements SerializerInterface
{
    /**
     * Converts a CellChoice into a JSON-encodable structure.
     *
     * @param CellChoice $choice
     * @param array      $options
     *
     * @return \stdClass
     */
    public function serialize($choice, array $options = [])
    {
        $choiceData = new \stdClass();
        $choiceData->text = $choice->getText();
        $choiceData->caseSensitive = $choice->isCaseSensitive();
        $choiceData->score = $choice->getScore();
        $choiceData->expected = $choice->isExpected();

        if ($choice->getFeedback()) {
            $choiceData->feedback = $choice->getFeedback();
        }

        return $choiceData;
    }

    /**
     * Converts raw data into a Keyword entity.
     *
     * @param \stdClass  $data
     * @param CellChoice $choice
     * @param array      $options
     *
     * @return CellChoice
     */
    public function deserialize($data, $choice = null, array $options = [])
    {
        if (empty($choice)) {
            $choice = new CellChoice();
        }

        $choice->setText($data->text);
        $choice->setCaseSensitive($data->caseSensitive);
        $choice->setScore($data->score);

        if (!empty($data->expected)) {
            $choice->setExpected($data->expected);
        }

        if (isset($data->feedback)) {
            $choice->setFeedback($data->feedback);
        }

        return $choice;
    }

    /**
     * Updates a collection of cel choices entities from raw data.
     * The one which are not in `$cellChoiceCollection` are removed from the entity collection.
     *
     * @param \stdClass[]  $cellChoiceCollection
     * @param CellChoice[] $cellChoiceEntities
     * @param array        $options
     *
     * @return Keyword[] - the list of updated Keyword entities (and without the one no longer in `$keywordCollection`)
     */
    public function deserializeCollection(array $cellChoiceCollection, array $cellChoiceEntities, array $options = [])
    {
        $cellChoices = [];

        foreach ($cellChoiceCollection as $cellChoiceData) {
            $cellChoice = null;

            // Searches for an existing keyword entity.
            foreach ($cellChoiceEntities as $entityCellChoice) {
                if ($entityCellChoice->getText() === $cellChoiceData->text
                    && $entityCellChoice->isCaseSensitive() === $cellChoiceData->caseSensitive) {
                    $cellChoice = $entityCellChoice;
                    break;
                }
            }

            // Update or create cell choice
            $cellChoices[] = $this->deserialize($cellChoiceData, $cellChoice, $options);
        }

        return $cellChoices;
    }
}
