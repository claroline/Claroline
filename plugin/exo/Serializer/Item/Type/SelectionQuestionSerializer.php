<?php

namespace UJM\ExoBundle\Serializer\Item\Type;

use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Entity\ItemType\SelectionQuestion;
use UJM\ExoBundle\Entity\Misc\Color;
use UJM\ExoBundle\Entity\Misc\ColorSelection;
use UJM\ExoBundle\Entity\Misc\Selection;
use UJM\ExoBundle\Library\Options\Transfer;
use UJM\ExoBundle\Library\Serializer\SerializerInterface;

/**
 * @DI\Service("ujm_exo.serializer.question_selection")
 */
class SelectionQuestionSerializer implements SerializerInterface
{
    /**
     * Converts a Selection question into a JSON-encodable structure.
     *
     * @param SelectionQuestion $selectionQuestion
     * @param array             $options
     *
     * @return \stdClass
     */
    public function serialize($selectionQuestion, array $options = [])
    {
        $questionData = new \stdClass();
        $questionData->text = $selectionQuestion->getText();
        $questionData->mode = $selectionQuestion->getMode();

        if ($selectionQuestion->getPenalty()) {
            $questionData->penalty = $selectionQuestion->getPenalty();
        }

        if ($selectionQuestion->getTries()) {
            $questionData->tries = $selectionQuestion->getTries();
        }

        switch ($selectionQuestion->getMode()) {
           case SelectionQuestion::MODE_FIND:
              $questionData->tries = $selectionQuestion->getTries();
              break;
           case SelectionQuestion::MODE_SELECT:
              $questionData->selections = $this->serializeSelections($selectionQuestion);
              break;
           case SelectionQuestion::MODE_HIGHLIGHT:
              $questionData->selections = $this->serializeSelections($selectionQuestion);
              $questionData->colors = $this->serializeColors($selectionQuestion);
              break;
        }

        if (in_array(Transfer::INCLUDE_SOLUTIONS, $options)) {
            $questionData->solutions = $this->serializeSolutions($selectionQuestion);
        }

        return $questionData;
    }

    /**
     * Converts raw data into a Selection question entity.
     *
     * @param \stdClass         $data
     * @param SelectionQuestion $selectionQuestion
     * @param array             $options
     *
     * @return ClozeQuestion
     */
    public function deserialize($data, $selectionQuestion = null, array $options = [])
    {
        if (empty($selectionQuestion)) {
            $selectionQuestion = new SelectionQuestion();
        }

        $selectionQuestion->setText($data->text);
        $selectionQuestion->setMode($data->mode);

        if (isset($data->tries)) {
            $selectionQuestion->setTries($data->tries);
        }

        // colors must be deserialized first because they might be useful for selections
        if (isset($data->colors)) {
            $this->deserializeColors($selectionQuestion, $data->colors);
        }

        if (property_exists($data, 'penalty')) {
            $selectionQuestion->setPenalty($data->penalty);
        }

        $options['selection_mode'] = $data->mode;

        if (isset($data->selections) && $data->mode !== 'find') {
            $this->deserializeSelections($selectionQuestion, $data->selections, $data->solutions, $options);
        }

        if (isset($data->solutions) && $data->mode === 'find') {
            $this->deserializeSolutions($selectionQuestion, $data->solutions, $options);
        }

        return $selectionQuestion;
    }

    /**
     * Serializes the Question holes.
     *
     * @param SelectionQuestion $selectionQuestion
     *
     * @return array
     */
    private function serializeSelections(SelectionQuestion $selectionQuestion)
    {
        return array_map(function (Selection $selection) use ($selectionQuestion) {
            $selectionData = new \stdClass();
            $selectionData->id = $selection->getUuid();
            $selectionData->begin = $selection->getBegin();
            $selectionData->end = $selection->getEnd();

            return $selectionData;
        }, $selectionQuestion->getSelections()->toArray());
    }

    private function serializeColors(SelectionQuestion $selectionQuestion)
    {
        return array_map(function (Color $color) {
            $colorData = new \stdClass();
            $colorData->id = $color->getUuid();
            $colorData->code = $color->getColorCode();

            return $colorData;
        }, $selectionQuestion->getColors()->toArray());
    }

    private function deserializeColors(SelectionQuestion $selectionQuestion, $colors)
    {
        $colorEntities = $selectionQuestion->getColors()->toArray();

        foreach ($colors as $colorData) {
            $color = null;

            // Searches for an existing color entity.
            foreach ($colorEntities as $entityIndex => $colorEntity) {
                /* @var Color $colorEntity */
              if ($colorEntity->getUuid() === $colorData->id) {
                  $color = $colorEntity;
                  unset($colorEntities[$entityIndex]);
                  break;
              }
            }

            $color = $color ?: new Color();
            $color->setUuid($colorData->id);
            $color->setColorCode($colorData->code);

            $selectionQuestion->addColor($color);
        }

        // Remaining color are no longer in the Question
        foreach ($colorEntities as $colorToRemove) {
            $selectionQuestion->removeColor($colorToRemove);
        }
    }

    /**
     * Deserializes Question selection.
     *
     * @param SelectionQuestion $selectionQuestion
     * @param array             $selections
     * @param array             $solutions
     * @param array             $options
     */
    private function deserializeSelections(SelectionQuestion $selectionQuestion, array $selections, array $solutions, array $options = [])
    {
        $selectionEntities = $selectionQuestion->getSelections()->toArray();

        foreach ($selections as $selectionData) {
            $selection = null;

            foreach ($selectionEntities as $entityIndex => $selectionEntity) {
                /** @var Selection $selectionEntity */
                if ($selectionEntity->getUuid() === $selectionData->id) {
                    $selection = $selectionEntity;
                    unset($selectionEntities[$entityIndex]);
                    break;
                }
            }

            $selection = $selection ?: new Selection();
            $selection->setUuid($selectionData->id);

            $solutionsD = array_values(array_filter($solutions, function ($solution) use ($selectionData) {
                return $solution->selectionId === $selectionData->id;
            }));

            if (isset($solutionsD[0]) && isset($solutionsD[0]->feedback)) {
                $selection->setFeedback($solutionsD[0]->feedback);
            }

            $selection->setBegin($selectionData->begin);
            $selection->setEnd($selectionData->end);

            foreach ($solutions as $solutionData) {
                if ($solutionData->selectionId === $selectionData->id) {
                    switch ($options['selection_mode']) {
                      case SelectionQuestion::MODE_SELECT:
                        $selection->setScore($solutionData->score);
                        break;
                      case SelectionQuestion::MODE_HIGHLIGHT:
                        $selection->setScore(0);
                        $this->deserializeColorSelection($selection, $solutionData->answers, $selectionQuestion->getColors()->toArray());
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
                if ($selectionEntity->getColor()->getUuid() === $answerData->colorId) {
                    $colorSelection = $selectionEntity;
                    unset($colorSelectionsEntities[$entityIndex]);
                    break;
                }
            }

            if (!$colorSelection) {
                $colorSelection = new ColorSelection();
            }

            $colorE = array_values(array_filter($colors, function ($color) use ($answerData) {
                return $color->getUuid() === $answerData->colorId;
            }))[0];

            $colorSelection->setColor($colorE);

            if (property_exists($answerData, 'feedback')) {
                $colorSelection->setFeedback($answerData->feedback);
            }

            $colorSelection->setSelection($selection);
            $colorSelection->setScore($answerData->score);
            $selection->addColorSelection($colorSelection);
        }

        foreach ($colorSelectionsEntities as $toRemove) {
            $selection->removeColorSelection($toRemove);
        }

        return $selection;
    }

    /**
     * Deserializes Question solutions.
     *
     * @param SelectionQuestion $selectionQuestion
     * @param array             $solutions
     * @param array             $options
     */
    private function deserializeSolutions(SelectionQuestion $selectionQuestion, array $solutions, array $options = [])
    {
        $selectionEntities = $selectionQuestion->getSelections()->toArray();

        foreach ($solutions as $solutionData) {
            $selection = null;

            foreach ($selectionEntities as $entityIndex => $selectionEntity) {
                /** @var Selection $selectionEntity */
                if ($selectionEntity->getUuid() === $solutionData->selectionId) {
                    $selection = $selectionEntity;
                    unset($selectionEntities[$entityIndex]);
                    break;
                }
            }

            $selection = $selection ?: new Selection();
            $selection->setUuid($solutionData->selectionId);

            if (isset($solutionData->feedback)) {
                $selection->setFeedback($solutionData->feedback);
            }

            $selection->setBegin($solutionData->begin);
            $selection->setEnd($solutionData->end);
            $selection->setScore($solutionData->score);
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
            return array_map(function (Selection $selection) {
                $solutionData = new \stdClass();
                $solutionData->selectionId = $selection->getUuid();
                $solutionData->score = $selection->getScore();
                $solutionData->begin = $selection->getBegin();
                $solutionData->end = $selection->getEnd();
                $solutionData->feedback = $selection->getFeedback();

                return $solutionData;
            }, $selectionQuestion->getSelections()->toArray());
         case SelectionQuestion::MODE_SELECT:
             return array_map(function (Selection $selection) {
                 $solutionData = new \stdClass();
                 $solutionData->selectionId = $selection->getUuid();
                 $solutionData->score = $selection->getScore();
                 $solutionData->feedback = $selection->getFeedback();

                 return $solutionData;
             }, $selectionQuestion->getSelections()->toArray());
         case SelectionQuestion::MODE_HIGHLIGHT:
             return array_map(function (Selection $selection) {
                 $solutionData = new \stdClass();
                 $solutionData->selectionId = $selection->getUuid();
                 $solutionData->answers = [];
                 foreach ($selection->getColorSelections()->toArray() as $colorSelection) {
                     $answer = new \stdClass();
                     $answer->score = $colorSelection->getScore();
                     $answer->colorId = $colorSelection->getColor()->getUuid();
                     $answer->feedback = $colorSelection->getFeedback();
                     $solutionData->answers[] = $answer;
                 }

                 return $solutionData;
             }, $selectionQuestion->getSelections()->toArray());
      }
    }
}
