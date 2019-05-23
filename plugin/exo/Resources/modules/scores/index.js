
// TODO : make dynamic registry
import ScoreFixed from '#/plugin/exo/scores/fixed'
import ScoreManual from '#/plugin/exo/scores/manual'
import ScoreNone from '#/plugin/exo/scores/none'
import ScoreRules from '#/plugin/exo/scores/rules'
import ScoreSum from '#/plugin/exo/scores/sum'

const SCORE_TYPES = {
  [ScoreNone.name]  : ScoreNone,
  [ScoreFixed.name] : ScoreFixed,
  [ScoreManual.name]: ScoreManual,
  [ScoreRules.name] : ScoreRules,
  [ScoreSum.name]   : ScoreSum
}

/**
 *
 * @param {object}          scoreRule
 * @param {CorrectedAnswer} correctedAnswer
 *
 * @return {number|null}
 */
function calculateScore(scoreRule, correctedAnswer) {
  const currentScore = SCORE_TYPES[scoreRule.type]
  if (currentScore) {
    let score = currentScore.calculate(scoreRule, correctedAnswer)
    if (null !== score) {
      score = correctedAnswer.getPenalties().reduce((score, penalty) => score - penalty, score)
    }

    return score
  }

  return null
}

/**
 *
 * @param {object}       scoreRule
 * @param {Answerable[]} expectedAnswers
 * @param {Answerable[]} allAnswers
 *
 * @return {number|null}
 */
function calculateTotal(scoreRule, expectedAnswers = [], allAnswers = []) {
  const currentScore = SCORE_TYPES[scoreRule.type]
  if (currentScore) {
    return currentScore.calculateTotal(scoreRule, expectedAnswers, allAnswers)
  }

  return null
}

export {
  SCORE_TYPES,
  calculateScore,
  calculateTotal
}
