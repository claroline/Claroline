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
use UJM\ExoBundle\Serializer\Item\Type\MatchQuestionSerializer;
use UJM\ExoBundle\Validator\JsonSchema\Attempt\AnswerData\MatchAnswerValidator;
use UJM\ExoBundle\Validator\JsonSchema\Item\Type\MatchQuestionValidator;

/**
 * Match question definition.
 */
class MatchDefinition extends AbstractDefinition
{
    /**
     * @var MatchQuestionValidator
     */
    private $validator;

    /**
     * @var MatchAnswerValidator
     */
    private $answerValidator;

    /**
     * @var MatchQuestionSerializer
     */
    private $serializer;

    /**
     * MatchDefinition constructor.
     */
    public function __construct(
        MatchQuestionValidator $validator,
        MatchAnswerValidator $answerValidator,
        MatchQuestionSerializer $serializer
    ) {
        $this->validator = $validator;
        $this->answerValidator = $answerValidator;
        $this->serializer = $serializer;
    }

    /**
     * Gets the match question mime-type.
     *
     * @return string
     */
    public static function getMimeType()
    {
        return ItemType::MATCH;
    }

    /**
     * Gets the match question entity.
     *
     * @return string
     */
    public static function getEntityClass()
    {
        return '\UJM\ExoBundle\Entity\ItemType\MatchQuestion';
    }

    /**
     * Gets the match question validator.
     *
     * @return MatchQuestionValidator
     */
    protected function getQuestionValidator()
    {
        return $this->validator;
    }

    /**
     * Gets the match answer validator.
     *
     * @return MatchAnswerValidator
     */
    protected function getAnswerValidator()
    {
        return $this->answerValidator;
    }

    /**
     * Gets the match question serializer.
     *
     * @return MatchQuestionSerializer
     */
    protected function getQuestionSerializer()
    {
        return $this->serializer;
    }

    /**
     * @param MatchQuestion $question
     * @param $answer
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

    public function getStatistics(AbstractItem $matchQuestion, array $answersData, $total)
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
        $proposals = $item->getProposals();
        $labels = $item->getLabels();
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
