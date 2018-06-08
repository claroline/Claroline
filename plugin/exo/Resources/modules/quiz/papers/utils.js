import {getDefinition} from '#/plugin/exo/items/item-types'
import {selectors as paperSelect} from '#/plugin/exo/quiz/papers/selectors'
import {
  RULE_TYPE_ALL,
  RULE_TYPE_MORE,
  RULE_TYPE_LESS,
  RULE_TYPE_BETWEEN,
  RULE_SOURCE_CORRECT,
  RULE_SOURCE_INCORRECT,
  RULE_TARGET_GLOBAL,
  RULE_TARGET_ANSWER
} from '#/plugin/exo/items/choice/constants'

import {
  SHOW_CORRECTION_AT_VALIDATION,
  SHOW_CORRECTION_AT_LAST_ATTEMPT,
  SHOW_CORRECTION_AT_DATE,
  SHOW_SCORE_AT_CORRECTION,
  SHOW_SCORE_AT_VALIDATION
} from '#/plugin/exo/quiz/enums'

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

    //then we need to compute it according to the total score if it exists...
    const totalScoreOn = paper.structure.parameters.totalScoreOn

    if (totalScoreOn) {
      //get the max score for the paper
      const maxScore = paperSelect.paperTotalAnswerScore(paper)

      total *= totalScoreOn/maxScore
    }

    return total
  }
}

//these functions are ported from php
function calculate(scoreRule, correctedAnswer) {
  let score = null
  const rulesData = {
    used: {}, // Only the first corresponding rule from each source (correct/incorrect answers) can be applied
    correctCount: 0,
    incorrectCount: 0,
    errorCount: 0
  }
  let isRuleValid = false

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
    case 'rules':
      score = 0
      rulesData.correctCount = correctedAnswer.getExpected().length + correctedAnswer.getExpectedMissing().length
      rulesData.incorrectCount = correctedAnswer.getUnexpected().length + correctedAnswer.getMissing().length
      rulesData.errorCount = correctedAnswer.getUnexpected().length

      scoreRule.rules.forEach(rule => {
        isRuleValid = false

        if (!rulesData.used[rule.source] && !(rule.source === RULE_SOURCE_CORRECT && scoreRule.noWrongChoice && rulesData.errorCount > 0)) {
          switch (rule.type) {
            case RULE_TYPE_ALL:
              isRuleValid = rule.source === RULE_SOURCE_INCORRECT ?
                rulesData.correctCount === 0 :
                rulesData.incorrectCount === 0
              break
            case RULE_TYPE_MORE:
              isRuleValid = rule.source === RULE_SOURCE_INCORRECT ?
                rulesData.incorrectCount > rule.count :
                rulesData.correctCount > rule.count
              break
            case RULE_TYPE_LESS:
              isRuleValid = rule.source === RULE_SOURCE_INCORRECT ?
                rulesData.incorrectCount < rule.count :
                rulesData.correctCount < rule.count
              break
            case RULE_TYPE_BETWEEN:
              isRuleValid = rule.source === RULE_SOURCE_INCORRECT ?
                rulesData.incorrectCount >= rule.countMin && rulesData.incorrectCount <= rule.countMax :
                rulesData.correctCount >= rule.countMin && rulesData.correctCount <= rule.countMax
              break
          }
          if (isRuleValid) {
            rulesData.used[rule.source] = true

            switch (rule.target) {
              case RULE_TARGET_GLOBAL:
                score += rule.points
                break
              case RULE_TARGET_ANSWER:
                score += rule.source === RULE_SOURCE_INCORRECT ?
                  rule.points * rulesData.incorrectCount :
                  rule.points * rulesData.correctCount
                break
            }
          }
        }
      })
      break
    case 'manual':
    case 'none':
      break
    default:
      //console.error('Unknown score type ' + scoreRule.type)
  }

  return score
}
