<?php

namespace UJM\ExoBundle\Library\Item\Definition;

use UJM\ExoBundle\Entity\Attempt\Answer;
use UJM\ExoBundle\Entity\ItemType\AbstractItem;
use UJM\ExoBundle\Entity\ItemType\ChoiceQuestion;
use UJM\ExoBundle\Entity\Misc\Choice;
use UJM\ExoBundle\Library\Attempt\AnswerPartInterface;
use UJM\ExoBundle\Library\Attempt\CorrectedAnswer;
use UJM\ExoBundle\Library\Csv\ArrayCompressor;
use UJM\ExoBundle\Library\Item\ItemType;
use UJM\ExoBundle\Serializer\Item\Type\ChoiceQuestionSerializer;
use UJM\ExoBundle\Validator\JsonSchema\Attempt\AnswerData\ChoiceAnswerValidator;
use UJM\ExoBundle\Validator\JsonSchema\Item\Type\ChoiceQuestionValidator;

/**
 * Choice question definition.
 */
class ChoiceDefinition extends AbstractDefinition
{
    /**
     * @var ChoiceQuestionValidator
     */
    private $validator;

    /**
     * @var ChoiceAnswerValidator
     */
    private $answerValidator;

    /**
     * @var ChoiceQuestionSerializer
     */
    private $serializer;

    /**
     * ChoiceDefinition constructor.
     */
    public function __construct(
        ChoiceQuestionValidator $validator,
        ChoiceAnswerValidator $answerValidator,
        ChoiceQuestionSerializer $serializer
    ) {
        $this->validator = $validator;
        $this->answerValidator = $answerValidator;
        $this->serializer = $serializer;
    }

    /**
     * Gets the choice question mime-type.
     *
     * @return string
     */
    public static function getMimeType()
    {
        return ItemType::CHOICE;
    }

    /**
     * Gets the choice question entity.
     *
     * @return string
     */
    public static function getEntityClass()
    {
        return '\UJM\ExoBundle\Entity\ItemType\ChoiceQuestion';
    }

    /**
     * Gets the choice question validator.
     *
     * @return ChoiceQuestionValidator
     */
    protected function getQuestionValidator()
    {
        return $this->validator;
    }

    /**
     * Gets the choice answer validator.
     *
     * @return ChoiceAnswerValidator
     */
    protected function getAnswerValidator()
    {
        return $this->answerValidator;
    }

    /**
     * Gets the choice question serializer.
     *
     * @return ChoiceQuestionSerializer
     */
    protected function getQuestionSerializer()
    {
        return $this->serializer;
    }

    /**
     * @param ChoiceQuestion $question
     * @param $answer
     *
     * @return CorrectedAnswer
     */
    public function correctAnswer(AbstractItem $question, $answer = [])
    {
        $corrected = new CorrectedAnswer();

        foreach ($question->getChoices() as $choice) {
            if (is_array($answer) && in_array($choice->getUuid(), $answer)) {
                // Choice has been selected by the user
                if (0 < $choice->getScore()) {
                    $corrected->addExpected($choice);
                } else {
                    $corrected->addUnexpected($choice);
                }
            } elseif (0 < $choice->getScore()) {
                // The choice is not selected but it's part of the correct answer
                $corrected->addMissing($choice);
            } else {
                // The choice is not selected as expected in correct answer
                $corrected->addExpectedMissing($choice);
            }
        }

        return $corrected;
    }

    /**
     * @param ChoiceQuestion $question
     *
     * @return AnswerPartInterface[]
     */
    public function expectAnswer(AbstractItem $question)
    {
        return array_filter($question->getChoices()->toArray(), function (Choice $choice) {
            return 0 < $choice->getScore();
        });
    }

    /**
     * @param ChoiceQuestion $question
     *
     * @return AnswerPartInterface[]
     */
    public function allAnswers(AbstractItem $question)
    {
        return $question->getChoices()->toArray();
    }

    public function getStatistics(AbstractItem $choiceQuestion, array $answersData, $total)
    {
        $choices = [];
        foreach ($answersData as $answerData) {
            foreach ($answerData as $choiceId) {
                $choices[$choiceId] = isset($choices[$choiceId]) ? $choices[$choiceId] + 1 : 1;
            }
        }

        return [
            'choices' => $choices,
            'total' => $total,
            'unanswered' => $total - count($answersData),
        ];
    }

    /**
     * Refreshes choice UUIDs.
     *
     * @param ChoiceQuestion $item
     */
    public function refreshIdentifiers(AbstractItem $item)
    {
        /** @var Choice $choice */
        foreach ($item->getChoices() as $choice) {
            $choice->refreshUuid();
        }
    }

    /**
     * Exports choice answers.
     *
     * @param ChoiceQuestion $item
     */
    public function getCsvAnswers(AbstractItem $item, Answer $answer)
    {
        $data = json_decode($answer->getData(), true);
        $answers = [];

        foreach ($item->getChoices() as $choice) {
            if (is_array($data) && in_array($choice->getUuid(), $data)) {
                $answers[] = $choice->getData();
            }
        }

        $compressor = new ArrayCompressor();

        return [$compressor->compress($answers)];
    }
}
