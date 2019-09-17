<?php

namespace UJM\ExoBundle\Serializer\Misc;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use UJM\ExoBundle\Entity\Misc\CellChoice;

/**
 * Serializer for CellChoice data.
 */
class CellChoiceSerializer
{
    use SerializerTrait;

    /**
     * Converts a CellChoice into a JSON-encodable structure.
     *
     * @param CellChoice $choice
     * @param array      $options
     *
     * @return array
     */
    public function serialize(CellChoice $choice, array $options = [])
    {
        $serialized = [
            'text' => $choice->getText(),
            'caseSensitive' => $choice->isCaseSensitive(),
            'score' => $choice->getScore(),
            'expected' => $choice->isExpected(), // TODO : should use the score to determine this
        ];

        if ($choice->getFeedback()) {
            $serialized['feedback'] = $choice->getFeedback();
        }

        return $serialized;
    }

    /**
     * Converts raw data into a Keyword entity.
     *
     * @param array      $data
     * @param CellChoice $choice
     * @param array      $options
     *
     * @return CellChoice
     */
    public function deserialize($data, CellChoice $choice = null, array $options = [])
    {
        if (empty($choice)) {
            $choice = new CellChoice();
        }
        $this->sipe('text', 'setText', $data, $choice);
        $this->sipe('caseSensitive', 'setCaseSensitive', $data, $choice);
        $this->sipe('score', 'setScore', $data, $choice);
        $this->sipe('expected', 'setExpected', $data, $choice);
        $this->sipe('feedback', 'setFeedback', $data, $choice);

        return $choice;
    }

    /**
     * Updates a collection of cel choices entities from raw data.
     * The one which are not in `$cellChoiceCollection` are removed from the entity collection.
     *
     * @param array        $cellChoiceCollection
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
                if ($entityCellChoice->getText() === $cellChoiceData['text']
                    && $entityCellChoice->isCaseSensitive() === $cellChoiceData['caseSensitive']) {
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
