<?php

namespace UJM\ExoBundle\Library\Item\Definition;

use UJM\ExoBundle\Entity\Attempt\Answer;
use UJM\ExoBundle\Entity\ItemType\AbstractItem;
use UJM\ExoBundle\Entity\ItemType\BooleanQuestion;
use UJM\ExoBundle\Entity\Misc\BooleanChoice;
use UJM\ExoBundle\Library\Attempt\CorrectedAnswer;
use UJM\ExoBundle\Library\Item\ItemType;
use UJM\ExoBundle\Serializer\Item\Type\BooleanQuestionSerializer;
use UJM\ExoBundle\Validator\JsonSchema\Attempt\AnswerData\BooleanAnswerValidator;
use UJM\ExoBundle\Validator\JsonSchema\Item\Type\BooleanQuestionValidator;

/**
 * Boolean choice question definition.
 */
class BooleanDefinition extends AbstractDefinition
{
    public function __construct(
        private readonly BooleanQuestionValidator $validator,
        private readonly BooleanAnswerValidator $answerValidator,
        private readonly BooleanQuestionSerializer $serializer
    ) {
    }

    public static function getMimeType(): string
    {
        return ItemType::BOOLEAN;
    }

    public static function getEntityClass(): string
    {
        return BooleanQuestion::class;
    }

    protected function getQuestionValidator(): BooleanQuestionValidator
    {
        return $this->validator;
    }

    protected function getAnswerValidator(): BooleanAnswerValidator
    {
        return $this->answerValidator;
    }

    protected function getQuestionSerializer(): BooleanQuestionSerializer
    {
        return $this->serializer;
    }

    public function correctAnswer(AbstractItem $question, mixed $answer = []): CorrectedAnswer
    {
        $corrected = new CorrectedAnswer();

        foreach ($question->getChoices() as $choice) {
            if (!empty($answer) && $choice->getUuid() === $answer) {
                // Choice has been selected by the user
                if (0 < $choice->getScore()) {
                    $corrected->addExpected($choice);
                } else {
                    $corrected->addUnexpected($choice);
                }
            } elseif (0 < $choice->getScore()) {
                // The choice is not selected but it's part of the correct answer
                $corrected->addMissing($choice);
            }
        }

        return $corrected;
    }

    /**
     * @param BooleanQuestion $question
     */
    public function expectAnswer(AbstractItem $question): array
    {
        return array_filter($question->getChoices()->toArray(), function (BooleanChoice $choice) {
            return 0 < $choice->getScore();
        });
    }

    /**
     * @param BooleanQuestion $question
     */
    public function allAnswers(AbstractItem $question): array
    {
        return $question->getChoices()->toArray();
    }

    /**
     * @param BooleanQuestion $question
     */
    public function getStatistics(AbstractItem $question, array $answersData, int $total): array
    {
        return [];
    }

    /**
     * Refreshes choice UUIDs.
     *
     * @param BooleanQuestion $question>
     */
    public function refreshIdentifiers(AbstractItem $question): void
    {
        /** @var BooleanChoice $choice */
        foreach ($question->getChoices() as $choice) {
            $choice->refreshUuid();
        }
    }

    /**
     * @param BooleanQuestion $question>
     */
    public function getCsvAnswers(AbstractItem $question, Answer $answer): array
    {
        $data = json_decode($answer->getData(), true);

        foreach ($question->getChoices() as $choice) {
            if ($data === $choice->getUuid()) {
                return [$choice->getData()];
            }
        }

        return [''];
    }
}
