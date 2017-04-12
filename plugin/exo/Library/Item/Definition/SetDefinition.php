<?php

namespace UJM\ExoBundle\Library\Item\Definition;

use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Entity\ItemType\AbstractItem;
use UJM\ExoBundle\Entity\ItemType\MatchQuestion;
use UJM\ExoBundle\Entity\Misc\Association;
use UJM\ExoBundle\Entity\Misc\Label;
use UJM\ExoBundle\Entity\Misc\Proposal;
use UJM\ExoBundle\Library\Attempt\CorrectedAnswer;
use UJM\ExoBundle\Library\Attempt\GenericPenalty;
use UJM\ExoBundle\Library\Item\ItemType;
use UJM\ExoBundle\Serializer\Item\Type\SetQuestionSerializer;
use UJM\ExoBundle\Transfer\Parser\ContentParserInterface;
use UJM\ExoBundle\Validator\JsonSchema\Attempt\AnswerData\SetAnswerValidator;
use UJM\ExoBundle\Validator\JsonSchema\Item\Type\SetQuestionValidator;

/**
 * Set question definition.
 *
 * @DI\Service("ujm_exo.definition.question_set")
 * @DI\Tag("ujm_exo.definition.item")
 */
class SetDefinition extends AbstractDefinition
{
    /**
     * @var SetQuestionValidator
     */
    private $validator;

    /**
     * @var SetAnswerValidator
     */
    private $answerValidator;

    /**
     * @var SetQuestionSerializer
     */
    private $serializer;

    /**
     * SetDefinition constructor.
     *
     * @param SetQuestionValidator  $validator
     * @param SetAnswerValidator    $answerValidator
     * @param SetQuestionSerializer $serializer
     *
     * @DI\InjectParams({
     *     "validator"       = @DI\Inject("ujm_exo.validator.question_set"),
     *     "answerValidator" = @DI\Inject("ujm_exo.validator.answer_set"),
     *     "serializer"      = @DI\Inject("ujm_exo.serializer.question_set")
     * })
     */
    public function __construct(
        SetQuestionValidator $validator,
        SetAnswerValidator $answerValidator,
        SetQuestionSerializer $serializer)
    {
        $this->validator = $validator;
        $this->answerValidator = $answerValidator;
        $this->serializer = $serializer;
    }

    /**
     * Gets the set question mime-type.
     *
     * @return string
     */
    public static function getMimeType()
    {
        return ItemType::SET;
    }

    /**
     * Gets the set question entity.
     *
     * @return string
     */
    public static function getEntityClass()
    {
        return '\UJM\ExoBundle\Entity\ItemType\MatchQuestion';
    }

    /**
     * Gets the set question validator.
     *
     * @return SetQuestionValidator
     */
    protected function getQuestionValidator()
    {
        return $this->validator;
    }

    /**
     * Gets the set answer validator.
     *
     * @return SetAnswerValidator
     */
    protected function getAnswerValidator()
    {
        return $this->answerValidator;
    }

    /**
     * Gets the set question serializer.
     *
     * @return SetQuestionSerializer
     */
    protected function getQuestionSerializer()
    {
        return $this->serializer;
    }

    /**
     * @param MatchQuestion $question
     * @param array         $answer
     *
     * @return CorrectedAnswer
     */
    public function correctAnswer(AbstractItem $question, $answer)
    {
        $corrected = new CorrectedAnswer();

        if (is_array($answer)) {
            foreach ($question->getAssociations() as $association) {
                $found = false;
                foreach ($answer as $index => $givenAnswer) {
                    if (null !== $association->getLabel()
                        && $association->getLabel()->getUuid() === $givenAnswer->setId
                        && $association->getProposal()->getUuid() === $givenAnswer->itemId
                    ) {
                        $found = true;
                        if (0 < $association->getScore()) {
                            $corrected->addExpected($association);
                        } else {
                            $corrected->addUnexpected($association);
                        }

                        unset($answer[$index]);
                    }
                }

                if (!$found && 0 < $association->getScore()) {
                    $corrected->addMissing($association);
                }
            }

            if (!empty($answer) && $question->getPenalty()) {
                // there are association not defined in the exercise
                $corrected->addPenalty(
                    new GenericPenalty(count($answer) * $question->getPenalty())
                );
            }
        }

        return $corrected;
    }

    /**
     * @param MatchQuestion $question
     *
     * @return array
     */
    public function expectAnswer(AbstractItem $question)
    {
        return array_filter($question->getAssociations()->toArray(), function (Association $association) {
            return 0 < $association->getScore();
        });
    }

    public function getStatistics(AbstractItem $setQuestion, array $answers)
    {
        // TODO: Implement getStatistics() method.

        return [];
    }

    /**
     * Refreshes items and sets UUIDs.
     *
     * @param MatchQuestion $item
     */
    public function refreshIdentifiers(AbstractItem $item)
    {
        /** @var Label $label */
        foreach ($item->getLabels() as $label) {
            $label->refreshUuid();
        }

        /** @var Proposal $proposal */
        foreach ($item->getProposals() as $proposal) {
            $proposal->refreshUuid();
        }
    }

    /**
     * Parses items and sets contents.
     *
     * @param ContentParserInterface $contentParser
     * @param \stdClass              $item
     */
    public function parseContents(ContentParserInterface $contentParser, \stdClass $item)
    {
        array_walk($item->items, function (\stdClass $item) use ($contentParser) {
            $item->data = $contentParser->parse($item->data);
        });

        array_walk($item->sets, function (\stdClass $set) use ($contentParser) {
            $set->data = $contentParser->parse($set->data);
        });
    }
}
