<?php

namespace UJM\ExoBundle\Library\Item\Definition;

use UJM\ExoBundle\Entity\Attempt\Answer;
use UJM\ExoBundle\Entity\ItemType\AbstractItem;
use UJM\ExoBundle\Entity\ItemType\MatchQuestion;
use UJM\ExoBundle\Entity\Misc\Association;
use UJM\ExoBundle\Entity\Misc\Label;
use UJM\ExoBundle\Entity\Misc\Proposal;
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
    public function __construct(
        private readonly SetQuestionValidator $validator,
        private readonly SetAnswerValidator $answerValidator,
        private readonly SetQuestionSerializer $serializer
    ) {
    }

    public static function getMimeType(): string
    {
        return ItemType::SET;
    }

    public static function getEntityClass(): string
    {
        return MatchQuestion::class;
    }

    protected function getQuestionValidator(): SetQuestionValidator
    {
        return $this->validator;
    }

    protected function getAnswerValidator(): SetAnswerValidator
    {
        return $this->answerValidator;
    }

    protected function getQuestionSerializer(): SetQuestionSerializer
    {
        return $this->serializer;
    }

    /**
     * @param MatchQuestion $question
     */
    public function correctAnswer(AbstractItem $question, mixed $answer): CorrectedAnswer
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
     */
    public function expectAnswer(AbstractItem $question): array
    {
        return array_filter($question->getAssociations()->toArray(), function (Association $association) {
            return 0 < $association->getScore();
        });
    }

    /**
     * @param MatchQuestion $question
     */
    public function allAnswers(AbstractItem $question): array
    {
        return $question->getAssociations()->toArray();
    }

    /**
     * @param MatchQuestion $question
     */
    public function getStatistics(AbstractItem $question, array $answersData, int $total): array
    {
        $sets = [];
        $unused = [];
        $unusedItems = [];

        foreach ($question->getProposals()->toArray() as $item) {
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
     * @param MatchQuestion $question
     */
    public function refreshIdentifiers(AbstractItem $question): void
    {
        /** @var Label $label */
        foreach ($question->getLabels() as $label) {
            $label->refreshUuid();
        }

        /** @var Proposal $proposal */
        foreach ($question->getProposals() as $proposal) {
            $proposal->refreshUuid();
        }
    }

    /**
     * @param MatchQuestion $question
     */
    public function getCsvAnswers(AbstractItem $question, Answer $answer): array
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
