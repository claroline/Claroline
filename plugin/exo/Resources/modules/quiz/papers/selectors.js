import {createSelector} from 'reselect'

import {getDefinition} from './../../items/item-types'

const quizId = state => state.quiz.id
const papersFetched = state => state.papers.isFetched
const currentPaperId = state => state.papers.current
const papers = state => state.papers.papers

const currentPaper = createSelector(
  papers,
  currentPaperId,
  (papers, currentPaperId) => {
    return papers[currentPaperId]
  }
)

const paperSteps = createSelector(
  currentPaper,
  (currentPaper) => currentPaper.structure.steps
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
    }
  }
  return scoreMax || 0
}

const paperScoreMax = paper => {
  if (totalScoreOn(paper)) {
    return totalScoreOn(paper)
  }

  let scoreMax = 0

  paper.structure.steps.map(step =>
    step.items.map(item => scoreMax += itemScoreMax(item))
  )

  return scoreMax
}

export const selectors = {
  quizId,
  papers,
  papersFetched,
  currentPaper,
  paperSteps,
  itemScoreMax,
  paperScoreMax,
  showScoreAt,
  showCorrectionAt,
  correctionDate,
  totalScoreOn
}
