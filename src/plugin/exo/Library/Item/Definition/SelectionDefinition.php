<?php

namespace UJM\ExoBundle\Library\Item\Definition;

use UJM\ExoBundle\Entity\Attempt\Answer;
use UJM\ExoBundle\Entity\ItemType\AbstractItem;
use UJM\ExoBundle\Entity\ItemType\SelectionQuestion;
use UJM\ExoBundle\Entity\Misc\Color;
use UJM\ExoBundle\Entity\Misc\Selection;
use UJM\ExoBundle\Library\Attempt\AnswerPartInterface;
use UJM\ExoBundle\Library\Attempt\CorrectedAnswer;
use UJM\ExoBundle\Library\Item\ItemType;
use UJM\ExoBundle\Serializer\Item\Type\SelectionQuestionSerializer;
use UJM\ExoBundle\Validator\JsonSchema\Attempt\AnswerData\SelectionAnswerValidator;
use UJM\ExoBundle\Validator\JsonSchema\Item\Type\SelectionQuestionValidator;

/**
 * Selection question definition.
 */
class SelectionDefinition extends AbstractDefinition
{
    /**
     * @var SelectionQuestionValidator
     */
    private $validator;

    /**
     * @var SelectionAnswerValidator
     */
    private $answerValidator;

    /**
     * @var SelectionQuestionSerializer
     */
    private $serializer;

    /**
     * SelectionDefinition constructor.
     */
    public function __construct(
        SelectionQuestionValidator $validator,
        SelectionAnswerValidator $answerValidator,
        SelectionQuestionSerializer $serializer
    ) {
        $this->validator = $validator;
        $this->answerValidator = $answerValidator;
        $this->serializer = $serializer;
    }

    /**
     * Gets the selection question mime-type.
     *
     * @return string
     */
    public static function getMimeType()
    {
        return ItemType::SELECTION;
    }

    /**
     * Gets the selection question entity.
     *
     * @return string
     */
    public static function getEntityClass()
    {
        return '\UJM\ExoBundle\Entity\ItemType\SelectionQuestion';
    }

    /**
     * Gets the selection question validator.
     *
     * @return SelectionQuestionValidator
     */
    protected function getQuestionValidator()
    {
        return $this->validator;
    }

    /**
     * Gets the selection answer validator.
     *
     * @return SelectionAnswerValidator
     */
    protected function getAnswerValidator()
    {
        return $this->answerValidator;
    }

    /**
     * Gets the selection question serializer.
     *
     * @return SelectionQuestionSerializer
     */
    protected function getQuestionSerializer()
    {
        return $this->serializer;
    }

    /**
     * @param SelectionQuestion $question
     * @param array             $answer
     *
     * @return CorrectedAnswer
     */
    public function correctAnswer(AbstractItem $question, $answers)
    {
        $corrected = new CorrectedAnswer();

        if (!is_null($answers)) {
            switch ($question->getMode()) {
               case $question::MODE_FIND:
                  $foundUuids = [];

                  foreach ($answers['positions'] as $position) {
                      foreach ($question->getSelections()->toArray() as $selection) {
                          if ($position >= $selection->getBegin() && $position <= $selection->getEnd()) {
                              if ($selection->getScore() > 0) {
                                  if (!in_array($selection->getUuid(), $foundUuids)) {
                                      $foundUuids[] = $selection->getUuid();
                                      $corrected->addExpected($selection);
                                  }
                              } else {
                                  $corrected->addUnexpected($selection);
                              }
                          }
                      }
                  }

                  $bestAnswers = $this->expectAnswer($question);
                  $uuids = array_map(function ($selection) {
                      return $selection->getUuid();
                  }, $bestAnswers);

                  if ($question->getPenalty() > 0) {
                      $penaltyTimes = $answers['tries'] - count($foundUuids);

                      for ($i = 0; $i < $penaltyTimes; ++$i) {
                          $unexpected = new Selection();
                          $unexpected->setScore(-1 * $question->getPenalty());
                          $corrected->addUnexpected($unexpected);
                      }
                  }

                  foreach ($uuids as $uuid) {
                      if (!in_array($uuid, $foundUuids)) {
                          $corrected->addMissing($question->getSelection($uuid));
                      }
                  }

                  return $corrected;

               case $question::MODE_SELECT:
                  foreach ($answers['selections'] as $selectionId) {
                      $selection = $question->getSelection($selectionId);
                      $selection->getScore() > 0 ? $corrected->addExpected($selection) : $corrected->addUnexpected($selection);
                  }

                  $bestAnswers = $this->expectAnswer($question);
                  $uuids = array_map(function ($selection) {
                      return $selection->getUuid();
                  }, $bestAnswers);

                  foreach ($uuids as $uuid) {
                      if (!in_array($uuid, $answers['selections'])) {
                          $corrected->addMissing($question->getSelection($uuid));
                      }
                  }

                  return $corrected;
               case $question::MODE_HIGHLIGHT:
                  $foundElements = [];

                  if (isset($answers['highlights'])) {
                      foreach ($answers['highlights'] as $highlightAnswer) {
                          if ($colorSelection = $question->getColorSelection(['color_uuid' => $highlightAnswer['colorId'], 'selection_uuid' => $highlightAnswer['selectionId']])) {
                              $colorSelection->getScore() > 0 ? $corrected->addExpected($colorSelection) : $corrected->addUnexpected($colorSelection);

                              $foundElements[] = ['color_uuid' => $highlightAnswer['colorId'], 'selection_uuid' => $highlightAnswer['selectionId']];
                          }
                      }
                  }
                  $bestAnswers = $this->expectAnswer($question);
                  $elements = array_map(function ($colorSelection) {
                      //id always returns null as of now
                      return ['color_uuid' => $colorSelection->getColor()->getUuid(), 'selection_uuid' => $colorSelection->getSelection()->getUuid()];
                  }, $bestAnswers);

                  $addedMissing = 0;

                  foreach ($elements as $element) {
                      if (!in_array($element, $foundElements)) {
                          ++$addedMissing;
                          $corrected->addMissing($question->getColorSelection(['color_uuid' => $element['color_uuid'], 'selection_uuid' => $element['selection_uuid']]));
                      }
                  }

                  if ($question->getPenalty() > 0) {
                      $penaltyTimes = count($question->getSelections()) - count($foundElements);

                      for ($i = 0; $i < $penaltyTimes; ++$i) {
                          $unexpected = new Selection();
                          $unexpected->setScore(-1 * $question->getPenalty());
                          $corrected->addUnexpected($unexpected);
                      }
                  }

                  return $corrected;
            }
        }
    }

    /**
     * @param SelectionQuestion $question
     *
     * @return AnswerPartInterface[]
     */
    public function expectAnswer(AbstractItem $question)
    {
        switch ($question->getMode()) {
           case $question::MODE_FIND:
           case $question::MODE_SELECT:
              $selections = $question->getSelections()->toArray();

              return array_filter($selections, function ($selection) {
                  return $selection->getScore() > 0;
              });
           case $question::MODE_HIGHLIGHT:
               return array_map(function (Selection $selection) {
                   $best = null;
                   $bestScore = 0;

                   foreach ($selection->getColorSelections() as $colorSelection) {
                       if ($colorSelection->getScore() > $bestScore) {
                           $bestScore = $colorSelection->getScore();
                           $best = $colorSelection;
                       }
                   }

                   return $best;
               }, $question->getSelections()->toArray());
        }
    }

    /**
     * @param SelectionQuestion $question
     *
     * @return AnswerPartInterface[]
     */
    public function allAnswers(AbstractItem $question)
    {
        return $question->getSelections()->toArray();
    }

    public function getStatistics(AbstractItem $selectionQuestion, array $answersData, $total)
    {
        // TODO: Implement getStatistics() method.

        return [];
    }

    /**
     * Refreshes selections and colors UUIDs.
     *
     * @param SelectionQuestion $item
     */
    public function refreshIdentifiers(AbstractItem $item)
    {
        /** @var Color $color */
        foreach ($item->getColors() as $color) {
            $color->refreshUuid();
        }

        /** @var Selection $selection */
        foreach ($item->getSelections() as $selection) {
            $selection->refreshUuid();
        }
    }

    public function getCsvAnswers(AbstractItem $item, Answer $answer)
    {
        $data = json_decode($answer->getData(), true);
        $strcsv = '';
        $answers = $this->correctAnswer($item, $data);

        switch ($item->getMode()) {
          case $item::MODE_FIND:
            $expected = $answers->getExpected();

            $strcsv = "[tries: {$data['tries']}, answers: [";
            $i = 0;

            foreach ($expected as $expectedAnswer) {
                if (0 !== $i) {
                    $strcsv .= ',';
                }
                $strcsv .= $this->getSelectedText($item, $expectedAnswer);
                ++$i;
            }

            $strcsv .= ']]';

            break;
          case $item::MODE_SELECT:
            $expected = $answers->getExpected();

            $strcsv = '[';
            $i = 0;

            foreach ($expected as $expectedAnswer) {
                if (0 !== $i) {
                    $strcsv .= ',';
                }
                $strcsv .= $this->getSelectedText($item, $expectedAnswer);
                ++$i;
            }

            $strcsv .= ']';

            break;
          case $item::MODE_HIGHLIGHT:
            $expected = $answers->getExpected();

            $strcsv = '[';
            $i = 0;

            foreach ($expected as $expectedAnswer) {
                if (0 !== $i) {
                    $strcsv .= ',';
                }
                $strcsv .= '{';
                $strcsv .= $this->getSelectedText($item, $expectedAnswer->getSelection()).': '.$expectedAnswer->getColor()->getColorCode();
                ++$i;
                $strcsv .= '}';
            }

            $strcsv .= ']';
        }

        return [$strcsv];
    }

    public function getSelectedText(AbstractItem $item, Selection $selection)
    {
        $text = $item->getText();

        return substr($text, $selection->getBegin(), $selection->getEnd() - $selection->getBegin());
    }
}
