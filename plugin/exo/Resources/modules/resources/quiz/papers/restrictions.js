import get from 'lodash/get'

import {constants} from '#/plugin/exo/resources/quiz/constants'

/**
 * Checks if the correction is available for the current user for a paper.
 *
 * @param {object|Paper.propTypes} paper
 * @param {boolean} admin
 *
 * @return {boolean}
 */
function showCorrection(paper, admin = false) {
  if (admin) {
    return true
  }

  const showCorrectionAt = get(paper, 'structure.parameters.showCorrectionAt')
  const correctionDate = get(paper, 'structure.parameters.correctionDate')

  if (showCorrectionAt === constants.QUIZ_RESULTS_AT_VALIDATION || showCorrectionAt === constants.QUIZ_RESULTS_AT_LAST_ATTEMPT){
    return paper.finished
  }

  if (showCorrectionAt === constants.QUIZ_RESULTS_AT_DATE){
    const today = Date.parse(new Date(Date.now()))
    const parsedCorrectionDate = Date.parse(correctionDate)

    return today >= parsedCorrectionDate
  }

  return false
}

/**
 * Checks if the score is available for the current user for a paper.
 *
 * @param {object|Paper.propTypes} paper
 * @param {boolean} admin
 *
 * @return {boolean}
 */
function showScore(paper, admin = false) {
  if (get(paper, 'structure.parameters.hasExpectedAnswers') && get(paper, 'structure.score.type') !== 'none') {
    if (admin) {
      return true
    }

    const showScoreAt = get(paper, 'structure.parameters.showScoreAt')
    if (showScoreAt === constants.QUIZ_SCORE_AT_CORRECTION) {
      return showCorrection(paper, admin)
    }

    if (showScoreAt === constants.QUIZ_SCORE_AT_VALIDATION) {
      return paper.finished
    }
  }

  return false
}

export {
  showCorrection,
  showScore
}
