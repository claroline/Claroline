<?php

namespace UJM\ExoBundle\Library\Item\Definition;

use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Entity\ItemType\AbstractItem;
use UJM\ExoBundle\Entity\ItemType\ChoiceQuestion;
use UJM\ExoBundle\Entity\Misc\Choice;
use UJM\ExoBundle\Library\Attempt\CorrectedAnswer;
use UJM\ExoBundle\Library\Item\ItemType;
use UJM\ExoBundle\Serializer\Item\Type\ChoiceQuestionSerializer;
use UJM\ExoBundle\Transfer\Parser\ContentParserInterface;
use UJM\ExoBundle\Validator\JsonSchema\Attempt\AnswerData\ChoiceAnswerValidator;
use UJM\ExoBundle\Validator\JsonSchema\Item\Type\ChoiceQuestionValidator;

/**
 * Choice question definition.
 *
 * @DI\Service("ujm_exo.definition.question_choice")
 * @DI\Tag("ujm_exo.definition.item")
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
     *
     * @param ChoiceQuestionValidator  $validator
     * @param ChoiceAnswerValidator    $answerValidator
     * @param ChoiceQuestionSerializer $serializer
     *
     * @DI\InjectParams({
     *     "validator"       = @DI\Inject("ujm_exo.validator.question_choice"),
     *     "answerValidator" = @DI\Inject("ujm_exo.validator.answer_choice"),
     *     "serializer"      = @DI\Inject("ujm_exo.serializer.question_choice")
     * })
     */
    public function __construct(
        ChoiceQuestionValidator $validator,
        ChoiceAnswerValidator $answerValidator,
        ChoiceQuestionSerializer $serializer)
    {
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
            }
        }

        return $corrected;
    }

    /**
     * @param ChoiceQuestion $question
     *
     * @return array
     */
    public function expectAnswer(AbstractItem $question)
    {
        return array_filter($question->getChoices()->toArray(), function (Choice $choice) {
            return 0 < $choice->getScore();
        });
    }

    public function getStatistics(AbstractItem $choiceQuestion, array $answersData)
    {
        $choices = [];

        foreach ($answersData as $answerData) {
            foreach ($answerData as $choiceId) {
                if (!isset($choices[$choiceId])) {
                    // First answer to have this solution
                    $choices[$choiceId] = new \stdClass();
                    $choices[$choiceId]->id = $choiceId;
                    $choices[$choiceId]->count = 0;
                }

                ++$choices[$choiceId]->count;
            }
        }

        return array_values($choices);
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
     * Parses choices contents.
     *
     * @param ContentParserInterface $contentParser
     * @param \stdClass              $item
     */
    public function parseContents(ContentParserInterface $contentParser, \stdClass $item)
    {
        array_walk($item->choices, function (\stdClass $choice) use ($contentParser) {
            $choice->data = $contentParser->parse($choice->data);
        });
    }
}
