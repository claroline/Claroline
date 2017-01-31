import {createSelector} from 'reselect'

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

export const selectors = {
  quizId,
  papers,
  papersFetched,
  currentPaper,
  paperSteps
}
