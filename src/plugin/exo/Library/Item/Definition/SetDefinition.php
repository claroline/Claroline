<?php

namespace UJM\ExoBundle\Library\Item\Definition;

use UJM\ExoBundle\Entity\Attempt\Answer;
use UJM\ExoBundle\Entity\ItemType\AbstractItem;
use UJM\ExoBundle\Entity\ItemType\MatchQuestion;
use UJM\ExoBundle\Entity\Misc\Association;
use UJM\ExoBundle\Entity\Misc\Label;
use UJM\ExoBundle\Entity\Misc\Proposal;
use UJM\ExoBundle\Library\Attempt\AnswerPartInterface;
use UJM\ExoBundle\Library\Attempt\CorrectedAnswer;
use UJM\ExoBundle\Library\Attempt\GenericPenalty;
use UJM\ExoBundle\Library\Csv\ArrayCompressor;
use UJM\ExoBundle\Library\Item\ItemType;
use UJM\ExoBundle\Serializer\Item\Type\SetQuestionSerializer;
use UJM\ExoBundle\Validator\JsonSchema\Attempt\AnswerData\SetAnswerValidator;
use UJM\ExoBundle\Validator\JsonSchema\Item\Type\SetQuestionValidator;

/**
 * Set question definition.
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
     */
    public function __construct(
        SetQuestionValidator $validator,
        SetAnswerValidator $answerValidator,
        SetQuestionSerializer $serializer
    ) {
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
                        && $association->getLabel()->getUuid() === $givenAnswer['setId']
                        && $association->getProposal()->getUuid() === $givenAnswer['itemId']
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
     * @return AnswerPartInterface[]
     */
    public function expectAnswer(AbstractItem $question)
    {
        return array_filter($question->getAssociations()->toArray(), function (Association $association) {
            return 0 < $association->getScore();
        });
    }

    /**
     * @param MatchQuestion $question
     *
     * @return AnswerPartInterface[]
     */
    public function allAnswers(AbstractItem $question)
    {
        return $question->getAssociations()->toArray();
    }

    public function getStatistics(AbstractItem $setQuestion, array $answersData, $total)
    {
        $sets = [];
        $unused = [];
        $unusedItems = [];

        foreach ($setQuestion->getProposals()->toArray() as $item) {
            $unusedItems[$item->getUuid()] = true;
        }
        foreach ($answersData as $answerData) {
            $unusedTemp = array_merge($unusedItems);

            foreach ($answerData as $setAnswer) {
                if (!isset($sets[$setAnswer['setId']])) {
                    $sets[$setAnswer['setId']] = [];
                }
                $sets[$setAnswer['setId']][$setAnswer['itemId']] = isset($sets[$setAnswer['setId']][$setAnswer['itemId']]) ?
                    $sets[$setAnswer['setId']][$setAnswer['itemId']] + 1 :
                    1;
                $unusedTemp[$setAnswer['itemId']] = false;
            }
            foreach ($unusedTemp as $itemId => $value) {
                if ($value) {
                    $unused[$itemId] = isset($unused[$itemId]) ? $unused[$itemId] + 1 : 1;
                }
            }
        }

        return [
            'sets' => $sets,
            'unused' => $unused,
            'total' => $total,
            'unanswered' => $total - count($answersData),
        ];
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

    public function getCsvAnswers(AbstractItem $item, Answer $answer)
    {
        $data = json_decode($answer->getData(), true);
        $answers = [];

        foreach ($data as $element) {
            $answers[] = "{$element['itemId']}: {$element['_itemData']}";
        }

        $compressor = new ArrayCompressor();

        return [$compressor->compress($answers)];
    }
}
