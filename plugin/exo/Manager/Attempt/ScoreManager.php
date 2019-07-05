<?php

namespace UJM\ExoBundle\Manager\Attempt;

use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Library\Attempt\AnswerPartInterface;
use UJM\ExoBundle\Library\Attempt\CorrectedAnswer;

/**
 * @DI\Service("ujm_exo.manager.score")
 */
class ScoreManager
{
    /**
     * Calculates the score obtained to a question based on a calculation rule.
     *
     * @param array           $scoreRule
     * @param CorrectedAnswer $correctedAnswer
     *
     * @return float|null the calculated score or null if it cannot be calculated automatically
     */
    public function calculate(array $scoreRule, CorrectedAnswer $correctedAnswer)
    {
        $score = null;

        switch ($scoreRule['type']) {
            case 'fixed':
                if (!empty($correctedAnswer->getMissing()) || !empty($correctedAnswer->getUnexpected())) {
                    // There are missing or unexpected answers => failure
                    $score = $scoreRule['failure'];
                } else {
                    // All is correct => success
                    $score = $scoreRule['success'];
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

                if (isset($scoreRule['total']) && 0 < $scoreRule['total']) {
                    // round score according to config
                    $answersTotal = array_reduce(
                        array_merge($correctedAnswer->getExpected(), $correctedAnswer->getMissing()),
                        function ($totalScore, AnswerPartInterface $answerPart) {
                            return $totalScore + $answerPart->getScore();
                        },
                        0
                    );

                    $score = ($score / $answersTotal) * $scoreRule['total'];
                }

                break;

            case 'rules':
                $score = 0;
                $used = [];
                $correctCount = count($correctedAnswer->getExpected()) + count($correctedAnswer->getExpectedMissing());
                $incorrectCount = count($correctedAnswer->getUnexpected()) + count($correctedAnswer->getMissing());
                $errorCount = count($correctedAnswer->getUnexpected());

                foreach ($scoreRule['rules'] as $rule) {
                    $isRuleValid = false;

                    if (!isset($used[$rule['source']]) && !('correct' === $rule['source'] && $scoreRule['noWrongChoice'] && 0 < $errorCount)) {
                        switch ($rule['type']) {
                            case 'all':
                                $isRuleValid = 'incorrect' === $rule['source'] ?
                                    0 === $correctCount :
                                    0 === $incorrectCount;
                                break;
                            case 'more':
                                $isRuleValid = 'incorrect' === $rule['source'] ?
                                    $incorrectCount > $rule['count'] :
                                    $correctCount > $rule['count'];
                                break;
                            case 'less':
                                $isRuleValid = 'incorrect' === $rule['source'] ?
                                    $incorrectCount < $rule['count'] :
                                    $correctCount < $rule['count'];
                                break;
                            case 'between':
                                $isRuleValid = 'incorrect' === $rule['source'] ?
                                    $incorrectCount >= $rule['countMin'] && $incorrectCount <= $rule['countMax'] :
                                    $correctCount >= $rule['countMin'] && $correctCount <= $rule['countMax'];
                                break;
                        }
                        if ($isRuleValid) {
                            $used[$rule['source']] = true;

                            switch ($rule['target']) {
                                case 'global':
                                    $score += $rule['points'];
                                    break;
                                case 'answer':
                                    $score += 'incorrect' === $rule['source'] ?
                                        $rule['points'] * $incorrectCount :
                                        $rule['points'] * $correctCount;
                                    break;
                            }
                        }
                    }
                }
                break;

            case 'manual':
            case 'none':
                break;

            default:
                throw new \LogicException("Unknown score type '{$scoreRule['type']}'.");
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
     * @param array                 $scoreRule
     * @param AnswerPartInterface[] $expectedAnswers
     * @param AnswerPartInterface[] $allAnswers
     *
     * @return float|null
     */
    public function calculateTotal(array $scoreRule, array $expectedAnswers, array $allAnswers = [])
    {
        $total = null;

        switch ($scoreRule['type']) {
            case 'fixed':
                $total = $scoreRule['success'];
                break;

            case 'sum':
                $total = 0;
                if (isset($scoreRule['total']) && 0 < $scoreRule['total']) {
                    $total = $scoreRule['total'];
                } else {
                    foreach ($expectedAnswers as $answer) {
                        $total += $answer->getScore();
                    }
                }

                break;

            case 'manual':
                $total = $scoreRule['max'];
                break;

            case 'rules':
                $max = [
                    'correct' => 0,
                    'incorrect' => 0,
                ];
                $nbChoices = count($allAnswers);

                // compute best score by source
                foreach ($scoreRule['rules'] as $rule) {
                    $score = 0;

                    switch ($rule['type']) {
                        case 'all':
                          $score = 'global' === $rule['target'] ? $rule['points'] : $rule['points'] * $nbChoices;
                          break;
                        case 'more':
                          if ('global' === $rule['target']) {
                              $score = $rule['count'] <= $nbChoices ? $rule['points'] : 0;
                          } else {
                              $score = $rule['count'] <= $nbChoices ? $rule['points'] * $nbChoices : 0;
                          }
                          break;
                        case 'less':
                          if ('global' === $rule['target']) {
                              $score = 0 < $rule['count'] ? $rule['points'] : 0;
                          } else {
                              if ($rule['count'] <= $nbChoices && 0 < $rule['count']) {
                                  $score = $rule['points'] * ($rule['count'] - 1);
                              } elseif ($rule['count'] > $nbChoices) {
                                  $score = $rule['points'] * $nbChoices;
                              }
                          }
                          break;
                        case 'between':
                          if ('global' === $rule['target']) {
                              $score = $rule['countMin'] <= $nbChoices ? $rule['points'] : 0;
                          } else {
                              if ($rule['countMax'] <= $nbChoices) {
                                  $score = $rule['points'] * $rule['countMax'];
                              } elseif ($rule['countMin'] <= $nbChoices && $rule['countMax'] >= $nbChoices) {
                                  $score = $rule['points'] * $nbChoices;
                              }
                          }
                          break;
                    }
                    if ($score > $max[$rule['source']]) {
                        $max[$rule['source']] = $score;
                    }
                }
                $total = $max['correct'] >= $max['incorrect'] ? $max['correct'] : $max['incorrect'];
                break;

            case 'none':
                break;

            default:
                throw new \LogicException("Unknown score type '{$scoreRule['type']}'.");
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
