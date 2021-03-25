<?php

namespace UJM\ExoBundle\Serializer\Item\Type;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use UJM\ExoBundle\Entity\ItemType\SelectionQuestion;
use UJM\ExoBundle\Entity\Misc\Color;
use UJM\ExoBundle\Entity\Misc\ColorSelection;
use UJM\ExoBundle\Entity\Misc\Selection;
use UJM\ExoBundle\Library\Options\Transfer;

class SelectionQuestionSerializer
{
    use SerializerTrait;

    /**
     * Converts a Selection question into a JSON-encodable structure.
     *
     * @return array
     */
    public function serialize(SelectionQuestion $selectionQuestion, array $options = [])
    {
        $serialized = [
            'text' => $selectionQuestion->getText(),
            'mode' => $selectionQuestion->getMode(),
        ];

        if ($selectionQuestion->getPenalty()) {
            $serialized['penalty'] = $selectionQuestion->getPenalty();
        }

        if ($selectionQuestion->getTries()) {
            $serialized['tries'] = $selectionQuestion->getTries();
        }

        switch ($selectionQuestion->getMode()) {
           case SelectionQuestion::MODE_FIND:
               $serialized['tries'] = $selectionQuestion->getTries();
              break;
           case SelectionQuestion::MODE_SELECT:
               $serialized['selections'] = $this->serializeSelections($selectionQuestion);
              break;
           case SelectionQuestion::MODE_HIGHLIGHT:
               $serialized['selections'] = $this->serializeSelections($selectionQuestion);
               $serialized['colors'] = $this->serializeColors($selectionQuestion);
              break;
        }

        if (in_array(Transfer::INCLUDE_SOLUTIONS, $options)) {
            $serialized['solutions'] = $this->serializeSolutions($selectionQuestion);
        }

        return $serialized;
    }

    public function getName()
    {
        return 'exo_question_selection';
    }

    /**
     * Converts raw data into a Selection question entity.
     *
     * @param array             $data
     * @param SelectionQuestion $selectionQuestion
     *
     * @return SelectionQuestion
     */
    public function deserialize($data, SelectionQuestion $selectionQuestion = null, array $options = [])
    {
        if (empty($selectionQuestion)) {
            $selectionQuestion = new SelectionQuestion();
        }
        $this->sipe('text', 'setText', $data, $selectionQuestion);
        $this->sipe('mode', 'setMode', $data, $selectionQuestion);
        $this->sipe('tries', 'setTries', $data, $selectionQuestion);
        $this->sipe('penalty', 'setPenalty', $data, $selectionQuestion);

        // colors must be deserialized first because they might be useful for selections
        if (isset($data['colors'])) {
            $this->deserializeColors($selectionQuestion, $data['colors']);
        }

        $options['selection_mode'] = $data['mode'];

        if (isset($data['selections']) && 'find' !== $data['mode']) {
            $this->deserializeSelections($selectionQuestion, $data['selections'], $data['solutions'], $options);
        }

        if (isset($data['solutions']) && 'find' === $data['mode']) {
            $this->deserializeSolutions($selectionQuestion, $data['solutions'], $options);
        }

        return $selectionQuestion;
    }

    /**
     * Serializes the Question holes.
     *
     * @return array
     */
    private function serializeSelections(SelectionQuestion $selectionQuestion)
    {
        return array_values(array_map(function (Selection $selection) {
            return [
                'id' => $selection->getUuid(),
                'begin' => $selection->getBegin(),
                'end' => $selection->getEnd(),
            ];
        }, $selectionQuestion->getSelections()->toArray()));
    }

    private function serializeColors(SelectionQuestion $selectionQuestion)
    {
        return array_values(array_map(function (Color $color) {
            return [
                'id' => $color->getUuid(),
                'code' => $color->getColorCode(),
            ];
        }, $selectionQuestion->getColors()->toArray()));
    }

    private function deserializeColors(SelectionQuestion $selectionQuestion, $colors)
    {
        $colorEntities = $selectionQuestion->getColors()->toArray();

        foreach ($colors as $colorData) {
            $color = null;

            // Searches for an existing color entity.
            foreach ($colorEntities as $entityIndex => $colorEntity) {
                /* @var Color $colorEntity */
                if ($colorEntity->getUuid() === $colorData['id']) {
                    $color = $colorEntity;
                    unset($colorEntities[$entityIndex]);
                    break;
                }
            }

            $color = $color ?: new Color();
            $color->setUuid($colorData['id']);
            $color->setColorCode($colorData['code']);

            $selectionQuestion->addColor($color);
        }

        // Remaining color are no longer in the Question
        foreach ($colorEntities as $colorToRemove) {
            $selectionQuestion->removeColor($colorToRemove);
        }
    }

    /**
     * Deserializes Question selection.
     */
    private function deserializeSelections(SelectionQuestion $selectionQuestion, array $selections, array $solutions, array $options = [])
    {
        $selectionEntities = $selectionQuestion->getSelections()->toArray();

        foreach ($selections as $selectionData) {
            $selection = null;

            foreach ($selectionEntities as $entityIndex => $selectionEntity) {
                /** @var Selection $selectionEntity */
                if ($selectionEntity->getUuid() === $selectionData['id']) {
                    $selection = $selectionEntity;
                    unset($selectionEntities[$entityIndex]);
                    break;
                }
            }

            $selection = $selection ?: new Selection();
            $selection->setUuid($selectionData['id']);

            $solutionsD = array_values(array_filter($solutions, function ($solution) use ($selectionData) {
                return $solution['selectionId'] === $selectionData['id'];
            }));

            if (isset($solutionsD[0]) && isset($solutionsD[0]['feedback'])) {
                $selection->setFeedback($solutionsD[0]['feedback']);
            }

            $selection->setBegin($selectionData['begin']);
            $selection->setEnd($selectionData['end']);

            foreach ($solutions as $solutionData) {
                if ($solutionData['selectionId'] === $selectionData['id']) {
                    switch ($options['selection_mode']) {
                      case SelectionQuestion::MODE_SELECT:
                        $selection->setScore($solutionData['score']);
                        break;
                      case SelectionQuestion::MODE_HIGHLIGHT:
                        $selection->setScore(0);
                        $this->deserializeColorSelection($selection, $solutionData['answers'], $selectionQuestion->getColors()->toArray());
                        break;
                      }
                }
            }

            $selectionQuestion->addSelection($selection);
        }

        // Remaining color are no longer in the Question
        foreach ($selectionEntities as $selectionToRemove) {
            $selectionQuestion->removeSelection($selectionToRemove);
        }
    }

    private function deserializeColorSelection(Selection $selection, array $answers, array $colors)
    {
        $colorSelectionsEntities = $selection->getColorSelections()->toArray();

        foreach ($answers as $answerData) {
            $colorSelection = null;

            foreach ($colorSelectionsEntities as $entityIndex => $selectionEntity) {
                if ($selectionEntity->getColor()->getUuid() === $answerData['colorId']) {
                    $colorSelection = $selectionEntity;
                    unset($colorSelectionsEntities[$entityIndex]);
                    break;
                }
            }

            if (!$colorSelection) {
                $colorSelection = new ColorSelection();
            }

            $colorE = array_values(array_filter($colors, function ($color) use ($answerData) {
                return $color->getUuid() === $answerData['colorId'];
            }))[0];

            $colorSelection->setColor($colorE);

            if (isset($answerData['feedback'])) {
                $colorSelection->setFeedback($answerData['feedback']);
            }

            $colorSelection->setSelection($selection);
            $colorSelection->setScore($answerData['score']);
            $selection->addColorSelection($colorSelection);
        }

        foreach ($colorSelectionsEntities as $toRemove) {
            $selection->removeColorSelection($toRemove);
        }

        return $selection;
    }

    /**
     * Deserializes Question solutions.
     */
    private function deserializeSolutions(SelectionQuestion $selectionQuestion, array $solutions, array $options = [])
    {
        $selectionEntities = $selectionQuestion->getSelections()->toArray();

        foreach ($solutions as $solutionData) {
            $selection = null;

            foreach ($selectionEntities as $entityIndex => $selectionEntity) {
                /** @var Selection $selectionEntity */
                if ($selectionEntity->getUuid() === $solutionData['selectionId']) {
                    $selection = $selectionEntity;
                    unset($selectionEntities[$entityIndex]);
                    break;
                }
            }

            $selection = $selection ?: new Selection();
            $selection->setUuid($solutionData['selectionId']);

            if (isset($solutionData['feedback'])) {
                $selection->setFeedback($solutionData['feedback']);
            }

            $selection->setBegin($solutionData['begin']);
            $selection->setEnd($solutionData['end']);
            $selection->setScore($solutionData['score']);
            $selectionQuestion->addSelection($selection);
        }

        // Remaining color are no longer in the Question
        foreach ($selectionEntities as $selectionToRemove) {
            $selectionQuestion->removeSelection($selectionToRemove);
        }
    }

    private function serializeSolutions(SelectionQuestion $selectionQuestion)
    {
        switch ($selectionQuestion->getMode()) {
         case SelectionQuestion::MODE_FIND:
            return array_values(array_map(function (Selection $selection) {
                return [
                    'selectionId' => $selection->getUuid(),
                    'score' => $selection->getScore(),
                    'begin' => $selection->getBegin(),
                    'end' => $selection->getEnd(),
                    'feedback' => $selection->getFeedback(),
                ];
            }, $selectionQuestion->getSelections()->toArray()));
         case SelectionQuestion::MODE_SELECT:
             return array_values(array_map(function (Selection $selection) {
                 return [
                     'selectionId' => $selection->getUuid(),
                     'score' => $selection->getScore(),
                     'feedback' => $selection->getFeedback(),
                 ];
             }, $selectionQuestion->getSelections()->toArray()));
         case SelectionQuestion::MODE_HIGHLIGHT:
             return array_values(array_map(function (Selection $selection) {
                 $solutionData = [
                     'selectionId' => $selection->getUuid(),
                     'answers' => [],
                 ];

                 foreach ($selection->getColorSelections()->toArray() as $colorSelection) {
                     $solutionData['answers'][] = [
                         'score' => $colorSelection->getScore(),
                         'colorId' => $colorSelection->getColor()->getUuid(),
                         'feedback' => $colorSelection->getFeedback(),
                     ];
                 }

                 return $solutionData;
             }, $selectionQuestion->getSelections()->toArray()));
      }
    }
}
