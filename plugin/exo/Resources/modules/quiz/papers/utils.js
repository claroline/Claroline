import {getDefinition} from '#/plugin/exo/items/item-types'
import {
  SHOW_CORRECTION_AT_VALIDATION,
  SHOW_CORRECTION_AT_LAST_ATTEMPT,
  SHOW_CORRECTION_AT_DATE,
  SHOW_SCORE_AT_CORRECTION,
  SHOW_SCORE_AT_VALIDATION
} from './../enums'

export const utils = {
  showCorrection(isAdmin, isFinished, showCorrectionAt, correctionDate) {
    if (isAdmin) {
      return true
    } else if (showCorrectionAt === SHOW_CORRECTION_AT_VALIDATION || showCorrectionAt === SHOW_CORRECTION_AT_LAST_ATTEMPT){
      return isFinished
    } else if (showCorrectionAt === SHOW_CORRECTION_AT_DATE){
      const today = Date.parse(new Date(Date.now()))
      const parsedCorrectionDate = Date.parse(correctionDate)
      return today >= parsedCorrectionDate
    } else {
      return false
    }
  },
  showScore(isAdmin, isFinished, showScoreAt, showCorrectionAt, correctionDate) {
    if (isAdmin) {
      return true
    } else if (showScoreAt === SHOW_SCORE_AT_CORRECTION){
      return utils.showCorrection(isAdmin, isFinished, showCorrectionAt, correctionDate)
    } else if (showScoreAt === SHOW_SCORE_AT_VALIDATION){
      return isFinished
    } else {
      return false
    }
  },
  computeScore(paper, answers) {
    let total = 0

    paper.structure.steps.forEach(step => {
      step.items.forEach(item => {
        //because some content object will thow some errors
        try  {
          const def = getDefinition(item.type)
          const correctedAnswer = def.getCorrectedAnswer(item, answers.find(answer => answer.questionId === item.id))
          total += calculate(item.score, correctedAnswer)
        } catch (e) {
          //console.error(e.message)
        }
      })
    })
    return total
  }
}

//these functions are ported from php
function calculate(scoreRule, correctedAnswer) {
  let score = null
  switch (scoreRule.type) {
    case 'fixed':
      score = correctedAnswer.getMissing().length > 0 || correctedAnswer.getUnexpected().length > 0 ?
        scoreRule.failure:
        scoreRule.success
      break
    case 'sum':
      score = 0
      correctedAnswer.getExpected().forEach(el => score += el.getScore())
      correctedAnswer.getUnexpected().forEach(el => score += el.getScore())
      correctedAnswer.getPenalties().forEach(el => score -= el.getScore())
      break
    case 'manual':
    case 'none':
      break
    default:
      //console.error('Unknown score type ' + scoreRule.type)
  }

  return score
}
