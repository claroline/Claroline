import {createSelector} from 'reselect'

import {getDefinition} from './../../items/item-types'

const quizId = state => state.quiz.id
const papers = state => state.papers.papers
const papersFetched = state => state.papers.isFetched
const currentPaperId = state => state.papers.current

const currentPaper = createSelector(
  papers,
  currentPaperId,
  (papers, currentId) => {
    return papers.find(paper => paper.id === currentId)
  }
)

const paperSteps = createSelector(
  currentPaper,
  (currentPaper) => currentPaper.structure.steps
)

const itemScoreMax = item => {
  let scoreMax

  if (item && item.score) {
    let expectedAnswers = []

    switch (item.score.type) {
      case 'manual':
        scoreMax = item.score.max
        break
      case 'fixed':
        scoreMax = item.score.success
        break
      case 'sum':
        expectedAnswers = getDefinition(item.type).expectAnswer(item)

        if (expectedAnswers.length > 0) {
          scoreMax = 0
          expectedAnswers.forEach(ca => {
            if (ca.score && ca.score > 0) {
              scoreMax += ca.score
            }
          })
        }
        break
    }
  }
  return scoreMax || 0
}

const paperScoreMax = paper => {
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
  paperScoreMax
}
