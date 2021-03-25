<?php

namespace UJM\ExoBundle\Library\Item\Definition;

use UJM\ExoBundle\Entity\Attempt\Answer;
use UJM\ExoBundle\Entity\ItemType\AbstractItem;
use UJM\ExoBundle\Entity\ItemType\BooleanQuestion;
use UJM\ExoBundle\Entity\Misc\BooleanChoice;
use UJM\ExoBundle\Library\Attempt\AnswerPartInterface;
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
    /**
     * @var BooleanQuestionValidator
     */
    private $validator;

    /**
     * @var BooleanAnswerValidator
     */
    private $answerValidator;

    /**
     * @var BooleanQuestionSerializer
     */
    private $serializer;

    /**
     * ChoiceDefinition constructor.
     */
    public function __construct(
        BooleanQuestionValidator $validator,
        BooleanAnswerValidator $answerValidator,
        BooleanQuestionSerializer $serializer
    ) {
        $this->validator = $validator;
        $this->answerValidator = $answerValidator;
        $this->serializer = $serializer;
    }

    /**
     * Gets the question mime-type.
     *
     * @return string
     */
    public static function getMimeType()
    {
        return ItemType::BOOLEAN;
    }

    /**
     * Gets the question entity.
     *
     * @return string
     */
    public static function getEntityClass()
    {
        return '\UJM\ExoBundle\Entity\ItemType\BooleanQuestion';
    }

    /**
     * Gets the boolean question validator.
     *
     * @return BooleanQuestionValidator
     */
    protected function getQuestionValidator()
    {
        return $this->validator;
    }

    /**
     * Gets the boolean answer validator.
     *
     * @return BooleanAnswerValidator
     */
    protected function getAnswerValidator()
    {
        return $this->answerValidator;
    }

    /**
     * Gets question serializer.
     *
     * @return BooleanQuestionSerializer
     */
    protected function getQuestionSerializer()
    {
        return $this->serializer;
    }

    /**
     * @param BooleanQuestion $question
     * @param $answer
     *
     * @return CorrectedAnswer
     */
    public function correctAnswer(AbstractItem $question, $answer = [])
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
     *
     * @return AnswerPartInterface[]
     */
    public function expectAnswer(AbstractItem $question)
    {
        return array_filter($question->getChoices()->toArray(), function (BooleanChoice $choice) {
            return 0 < $choice->getScore();
        });
    }

    /**
     * @param BooleanQuestion $question
     *
     * @return AnswerPartInterface[]
     */
    public function allAnswers(AbstractItem $question)
    {
        return $question->getChoices()->toArray();
    }

    public function getStatistics(AbstractItem $question, array $answersData, $total)
    {
        // TODO: Implement getStatistics() method.

        return [];
    }

    /**
     * Refreshes choice UUIDs.
     *
     * @param BooleanQuestion $item
     */
    public function refreshIdentifiers(AbstractItem $item)
    {
        /** @var BooleanChoice $choice */
        foreach ($item->getChoices() as $choice) {
            $choice->refreshUuid();
        }
    }

    public function getCsvAnswers(AbstractItem $item, Answer $answer)
    {
        $data = json_decode($answer->getData(), true);
        $answer = null;

        foreach ($item->getChoices() as $choice) {
            if ($data === $choice->getUuid()) {
                return [$choice->getData()];
            }
        }

        return [''];
    }
}
