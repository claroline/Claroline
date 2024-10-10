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
use UJM\ExoBundle\Serializer\Item\Type\MatchQuestionSerializer;
use UJM\ExoBundle\Validator\JsonSchema\Attempt\AnswerData\MatchAnswerValidator;
use UJM\ExoBundle\Validator\JsonSchema\Item\Type\MatchQuestionValidator;

/**
 * Match question definition.
 */
class MatchDefinition extends AbstractDefinition
{
    public function __construct(
        private readonly MatchQuestionValidator $validator,
        private readonly MatchAnswerValidator $answerValidator,
        private readonly MatchQuestionSerializer $serializer
    ) {
    }

    public static function getMimeType(): string
    {
        return ItemType::MATCH;
    }

    public static function getEntityClass(): string
    {
        return MatchQuestion::class;
    }

    protected function getQuestionValidator(): MatchQuestionValidator
    {
        return $this->validator;
    }

    protected function getAnswerValidator(): MatchAnswerValidator
    {
        return $this->answerValidator;
    }

    protected function getQuestionSerializer(): MatchQuestionSerializer
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
                    if ($association->getProposal()->getUuid() === $givenAnswer['firstId'] && $association->getLabel()->getUuid() === $givenAnswer['secondId']) {
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
        } else {
            foreach ($question->getAssociations() as $association) {
                if (0 < $association->getScore()) {
                    $corrected->addMissing($association);
                }
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
        $matches = [];

        foreach ($answersData as $answerData) {
            foreach ($answerData as $matchAnswer) {
                if (!empty($matchAnswer['firstId']) && !empty($matchAnswer['secondId'])) {
                    if (!isset($matches[$matchAnswer['firstId']])) {
                        $matches[$matchAnswer['firstId']] = [];
                    }
                    $matches[$matchAnswer['firstId']][$matchAnswer['secondId']] = isset($matches[$matchAnswer['firstId']][$matchAnswer['secondId']]) ?
                        $matches[$matchAnswer['firstId']][$matchAnswer['secondId']] + 1 :
                        1;
                }
            }
        }

        return [
            'matches' => $matches,
            'total' => $total,
            'unanswered' => $total - count($answersData),
        ];
    }

    /**
     * Refreshes items UUIDs.
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
        $proposals = $question->getProposals();
        $labels = $question->getLabels();
        $answers = [];

        foreach ($data as $pair) {
            $answerPair = '[';

            foreach ($proposals as $proposal) {
                if ($proposal->getUuid() === $pair['firstId']) {
                    $answerPair .= $proposal->getData();
                }
            }

            $answerPair .= ';';

            foreach ($labels as $label) {
                if ($label->getUuid() === $pair['secondId']) {
                    $answerPair .= $label->getData();
                }
            }

            $answerPair .= ']';
            $answers[] = $answerPair;
        }

        $compressor = new ArrayCompressor();

        return [$compressor->compress($answers)];
    }
}
