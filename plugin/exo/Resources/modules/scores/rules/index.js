import {trans} from '#/main/app/intl/translation'

import {constants} from '#/plugin/exo/scores/rules/constants'

const SCORE_RULES = 'rules'

export default {
  name: SCORE_RULES,
  meta: {
    label: trans('score_rules', {}, 'quiz'),
    description: trans('score_rules_desc', {}, 'quiz')
  },

  hasAnswerScores: false,

  configure: () => [
    {
      name: 'noWrongChoice',
      label: trans('no_wrong_checked_choice_info', {}, 'quiz'),
      type: 'boolean'
    }, {
      name: 'rules',
      label: trans('rules', {}, 'quiz'),
      type: 'score_rules',
      required: true
    }
  ],

  calculate: (scoreRule, correctedAnswer) => {
    let score = 0

    const used = {} // Only the first corresponding rule from each source (correct/incorrect answers) can be applied
    const correctCount = correctedAnswer.getExpected().length + correctedAnswer.getExpectedMissing().length
    const incorrectCount = correctedAnswer.getUnexpected().length + correctedAnswer.getMissing().length
    const errorCount = correctedAnswer.getUnexpected().length

    scoreRule.rules.forEach(rule => {
      let isRuleValid = false

      if (!used[rule.source] && !(rule.source === constants.RULE_SOURCE_CORRECT && scoreRule.noWrongChoice && errorCount > 0)) {
        switch (rule.type) {
          case constants.RULE_TYPE_ALL:
            isRuleValid = rule.source === constants.RULE_SOURCE_INCORRECT ?
              correctCount === 0 :
              incorrectCount === 0
            break

          case constants.RULE_TYPE_MORE:
            isRuleValid = rule.source === constants.RULE_SOURCE_INCORRECT ?
              incorrectCount > rule.count :
              correctCount > rule.count
            break

          case constants.RULE_TYPE_LESS:
            isRuleValid = rule.source === constants.RULE_SOURCE_INCORRECT ?
              incorrectCount < rule.count :
              correctCount < rule.count
            break

          case constants.RULE_TYPE_BETWEEN:
            isRuleValid = rule.source === constants.RULE_SOURCE_INCORRECT ?
              incorrectCount >= rule.countMin && incorrectCount <= rule.countMax :
              correctCount >= rule.countMin && correctCount <= rule.countMax
            break
        }

        if (isRuleValid) {
          used[rule.source] = true

          switch (rule.target) {
            case constants.RULE_TARGET_GLOBAL:
              score += rule.points
              break

            case constants.RULE_TARGET_ANSWER:
              score += rule.source === constants.RULE_SOURCE_INCORRECT ?
                rule.points * incorrectCount :
                rule.points * correctCount
              break
          }
        }
      }
    })

    return score
  },

  calculateTotal: (scoreRule, expectedAnswer, allAnswers) => {
    const rulesData = {
      nbChoices: 0,
      max: {
        [constants.RULE_SOURCE_CORRECT]: 0,
        [constants.RULE_SOURCE_INCORRECT]: 0
      }
    }

    rulesData.nbChoices = allAnswers ? allAnswers.length : 0

    // compute best score by source
    scoreRule.rules.forEach(rule => {
      let score = 0

      switch (rule.type) {
        case constants.RULE_TYPE_ALL:
          score = rule.target === constants.RULE_TARGET_GLOBAL ?
            rule.points :
            rule.points * rulesData.nbChoices
          break

        case constants.RULE_TYPE_MORE:
          if (rule.target === constants.RULE_TARGET_GLOBAL) {
            score = rule.count <= rulesData.nbChoices ? rule.points : 0
          } else {
            score = rule.count <= rulesData.nbChoices ? rule.points * rulesData.nbChoices : 0
          }
          break

        case constants.RULE_TYPE_LESS:
          if (rule.target === constants.RULE_TARGET_GLOBAL) {
            score = rule.count > 0 ? rule.points : 0
          } else {
            if (rule.count <= rulesData.nbChoices && rule.count > 0) {
              score = rule.points * (rule.count - 1)
            } else if (rule.count > rulesData.nbChoices) {
              score = rule.points * rulesData.nbChoices
            }
          }
          break

        case constants.RULE_TYPE_BETWEEN:
          if (rule.target === constants.RULE_TARGET_GLOBAL) {
            score = rule.countMin <= rulesData.nbChoices ? rule.points : 0
          } else {
            if (rule.countMax <= rulesData.nbChoices) {
              score = rule.points * rule.countMax
            } else if (rule.countMin <= rulesData.nbChoices && rule.countMax >= rulesData.nbChoices) {
              score = rule.points * rulesData.nbChoices
            }
          }
          break
      }
      if (score > rulesData.max[rule.source]) {
        rulesData.max[rule.source] = score
      }
    })

    return rulesData.max[constants.RULE_SOURCE_CORRECT] >= rulesData.max[constants.RULE_SOURCE_INCORRECT] ?
      rulesData.max[constants.RULE_SOURCE_CORRECT] :
      rulesData.max[constants.RULE_SOURCE_INCORRECT]
  }
}
