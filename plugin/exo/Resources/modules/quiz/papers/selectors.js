import {createSelector} from 'reselect'

import {getDefinition} from '#/plugin/exo/items/item-types'
import {
  RULE_TYPE_ALL,
  RULE_TYPE_MORE,
  RULE_TYPE_LESS,
  RULE_TYPE_BETWEEN,
  RULE_SOURCE_CORRECT,
  RULE_SOURCE_INCORRECT,
  RULE_TARGET_GLOBAL
} from '#/plugin/exo/items/choice/constants'

import {select as quizSelectors} from '#/plugin/exo/quiz/selectors'

const quizId = quizSelectors.id
const papersFetched = createSelector(
  [quizSelectors.papers],
  (papers) => papers.isFetched
)
const papers = createSelector(
  [quizSelectors.papers],
  (papers) => papers.papers
)

const currentPaper = createSelector(
  [quizSelectors.papers],
  (papers) => papers.current
)

const showScoreAt = paper => {
  return paper.structure.parameters.showScoreAt
}

const showCorrectionAt = paper => {
  return paper.structure.parameters.showCorrectionAt
}

const correctionDate = paper => {
  return paper.structure.parameters.correctionDate
}

const totalScoreOn = paper => {
  return paper.structure.parameters.totalScoreOn && paper.structure.parameters.totalScoreOn > 0 ? paper.structure.parameters.totalScoreOn : null
}

const itemScoreMax = item => {
  let scoreMax
  const rulesData = {
    nbChoices: 0,
    max: {
      [RULE_SOURCE_CORRECT]: 0,
      [RULE_SOURCE_INCORRECT]: 0
    }
  }
  let score = 0

  if (item && item.score) {
    switch (item.score.type) {
      case 'manual':
        scoreMax = item.score.max
        break
      case 'fixed':
        scoreMax = item.score.success
        break
      case 'sum':
        scoreMax = getDefinition(item.type).getCorrectedAnswer(item).getMissing().reduce((sum, el) => sum += el.getScore(), 0)
        break
      case 'rules':
        rulesData.nbChoices = item.choices ? item.choices.length : 0

        // compute best score by source
        item.score.rules.forEach(rule => {
          score = 0

          switch (rule.type) {
            case RULE_TYPE_ALL:
              score = rule.target === RULE_TARGET_GLOBAL ?
                rule.points :
                rule.points * rulesData.nbChoices
              break
            case RULE_TYPE_MORE:
              if (rule.target === RULE_TARGET_GLOBAL) {
                score = rule.count <= rulesData.nbChoices ? rule.points : 0
              } else {
                score = rule.count <= rulesData.nbChoices ? rule.points * rulesData.nbChoices : 0
              }
              break
            case RULE_TYPE_LESS:
              if (rule.target === RULE_TARGET_GLOBAL) {
                score = rule.count > 0 ? rule.points : 0
              } else {
                if (rule.count <= rulesData.nbChoices && rule.count > 0) {
                  score = rule.points * (rule.count - 1)
                } else if (rule.count > rulesData.nbChoices) {
                  score = rule.points * rulesData.nbChoices
                }
              }
              break
            case RULE_TYPE_BETWEEN:
              if (rule.target === RULE_TARGET_GLOBAL) {
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
        scoreMax = rulesData.max[RULE_SOURCE_CORRECT] >= rulesData.max[RULE_SOURCE_INCORRECT] ?
          rulesData.max[RULE_SOURCE_CORRECT] :
          rulesData.max[RULE_SOURCE_INCORRECT]
        break
    }
  }

  return scoreMax || 0
}

const paperTotalAnswerScore = paper => {
  let scoreMax = 0

  paper.structure.steps.map(step =>
    step.items.map(item => scoreMax += itemScoreMax(item))
  )

  return scoreMax
}

const paperScoreMax = paper => {
  if (totalScoreOn(paper)) {
    return totalScoreOn(paper)
  }

  return paperTotalAnswerScore(paper)
}

const paperItemsCount = paper => {
  let count = 0
  paper.structure.steps.forEach(step => count += step.items.length)

  return count
}

export const selectors = {
  quizId,
  papers,
  papersFetched,
  currentPaper,
  itemScoreMax,
  paperScoreMax,
  showScoreAt,
  showCorrectionAt,
  correctionDate,
  totalScoreOn,
  paperTotalAnswerScore,
  paperItemsCount
}
