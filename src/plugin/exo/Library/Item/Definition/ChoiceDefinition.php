<?php

namespace UJM\ExoBundle\Library\Item\Definition;

use UJM\ExoBundle\Entity\Attempt\Answer;
use UJM\ExoBundle\Entity\ItemType\AbstractItem;
use UJM\ExoBundle\Entity\ItemType\ChoiceQuestion;
use UJM\ExoBundle\Entity\Misc\Choice;
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
    public function __construct(
        private readonly ChoiceQuestionValidator $validator,
        private readonly ChoiceAnswerValidator $answerValidator,
        private readonly ChoiceQuestionSerializer $serializer
    ) {
    }

    public static function getMimeType(): string
    {
        return ItemType::CHOICE;
    }

    public static function getEntityClass(): string
    {
        return ChoiceQuestion::class;
    }

    protected function getQuestionValidator(): ChoiceQuestionValidator
    {
        return $this->validator;
    }

    protected function getAnswerValidator(): ChoiceAnswerValidator
    {
        return $this->answerValidator;
    }

    protected function getQuestionSerializer(): ChoiceQuestionSerializer
    {
        return $this->serializer;
    }

    /**
     * @param ChoiceQuestion $question
     */
    public function correctAnswer(AbstractItem $question, mixed $answer = []): CorrectedAnswer
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
     */
    public function expectAnswer(AbstractItem $question): array
    {
        return array_filter($question->getChoices()->toArray(), function (Choice $choice) {
            return 0 < $choice->getScore();
        });
    }

    /**
     * @param ChoiceQuestion $question
     */
    public function allAnswers(AbstractItem $question): array
    {
        return $question->getChoices()->toArray();
    }

    /**
     * @param ChoiceQuestion $question
     */
    public function getStatistics(AbstractItem $question, array $answersData, int $total): array
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
     * @param ChoiceQuestion $question
     */
    public function refreshIdentifiers(AbstractItem $question): void
    {
        /** @var Choice $choice */
        foreach ($question->getChoices() as $choice) {
            $choice->refreshUuid();
        }
    }

    /**
     * Exports choice answers.
     *
     * @param ChoiceQuestion $question
     */
    public function getCsvAnswers(AbstractItem $question, Answer $answer): array
    {
        $data = json_decode($answer->getData(), true);
        $answers = [];

        foreach ($question->getChoices() as $choice) {
            if (is_array($data) && in_array($choice->getUuid(), $data)) {
                $answers[] = $choice->getData();
            }
        }

        $compressor = new ArrayCompressor();

        return [$compressor->compress($answers)];
    }
}
