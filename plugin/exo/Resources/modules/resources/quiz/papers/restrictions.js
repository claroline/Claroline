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

  const showCorrectionAt = paper.parameters.showCorrectionAt
  const correctionDate = paper.parameters.correctionDate

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
  if (admin) {
    return true
  }

  const showScoreAt = paper.parameters.showScoreAt
  if (showScoreAt === constants.QUIZ_SCORE_AT_CORRECTION){
    return showCorrection(paper, admin)
  }

  if (showScoreAt === constants.QUIZ_SCORE_AT_VALIDATION){
    return paper.finished
  }

  return false
}

export {
  showCorrection,
  showScore
}
