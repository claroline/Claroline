<?php

namespace UJM\ExoBundle\Manager\Attempt;

use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Library\Attempt\CorrectedAnswer;

/**
 * @DI\Service("ujm_exo.manager.score")
 */
class ScoreManager
{
    /**
     * Calculates the score obtained to a question based on a calculation rule.
     *
     * @param \stdClass       $scoreRule
     * @param CorrectedAnswer $correctedAnswer
     *
     * @return float|null the calculated score or null if it cannot be calculated automatically
     */
    public function calculate(\stdClass $scoreRule, CorrectedAnswer $correctedAnswer)
    {
        $score = null;
        switch ($scoreRule->type) {
            case 'fixed':
                if (!empty($correctedAnswer->getMissing()) || !empty($correctedAnswer->getUnexpected())) {
                    // There are missing or unexpected answers => failure
                    $score = $scoreRule->failure;
                } else {
                    // All is correct => success
                    $score = $scoreRule->success;
                }
                break;

            case 'sum':
                $score = 0;
                // We get the score of all the answers given, right or wrong.
                foreach ($correctedAnswer->getExpected() as $el) {
                    $score += $el->getScore();
                }

                foreach ($correctedAnswer->getUnexpected() as $el) {
                    $score += $el->getScore();
                }
                break;

            case 'manual':
            case 'none':
                break;

            default:
                throw new \LogicException("Unknown score type '{$scoreRule->type}'.");
                break;
        }

        if (null !== $score) {
            $score = $this->applyPenalties($score, $correctedAnswer);
        }

        return $score;
    }

    /**
     * Calculates the maximum score for a question based on a calculation rule
     * and the expected answer.
     *
     * @param \stdClass $scoreRule
     * @param array     $expectedAnswers
     *
     * @return float|null
     */
    public function calculateTotal(\stdClass $scoreRule, array $expectedAnswers)
    {
        $total = null;
        switch ($scoreRule->type) {
            case 'fixed':
                $total = $scoreRule->success;
                break;

            case 'sum':
                $total = 0;
                foreach ($expectedAnswers as $answer) {
                    $total += $answer->getScore();
                }

                break;

            case 'manual':
                $total = $scoreRule->max;
                break;

            default:
                throw new \LogicException("Unknown score type '{$scoreRule->type}'.");
                break;
        }

        return $total;
    }

    /**
     * Applies hint penalties to a score.
     *
     * @param float           $score
     * @param CorrectedAnswer $correctedAnswer
     *
     * @return float
     */
    public function applyPenalties($score, CorrectedAnswer $correctedAnswer)
    {
        $penalties = $correctedAnswer->getPenalties();
        foreach ($penalties as $penalty) {
            $score -= $penalty->getPenalty();
        }

        return $score;
    }
}
