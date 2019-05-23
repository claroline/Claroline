import {createSelector} from 'reselect'
import get from 'lodash/get'

import {selectors as baseSelectors} from '#/plugin/exo/resources/quiz/store/selectors'

const STORE_NAME = 'papers'
const LIST_NAME = `${baseSelectors.STORE_NAME}.${STORE_NAME}.list`

const quizId = baseSelectors.id

const quizHasScore = createSelector(
  [baseSelectors.quiz],
  (quiz) => quiz.score && 'none' !== quiz.score.type
)

const papers = createSelector(
  [baseSelectors.resource],
  (resourceState) => resourceState[STORE_NAME]
)

const currentPaper = createSelector(
  [papers],
  (papersState) => papersState.current
)

const currentParameters = createSelector(
  [currentPaper],
  (currentPaper) => get(currentPaper, 'structure.parameters') || {}
)

const currentNumbering = createSelector(
  [currentParameters],
  (currentParameters) => currentParameters.numbering
)

const showExpectedAnswers = createSelector(
  [currentParameters],
  (parameters) => parameters.showFullCorrection || false
)

const showStatistics = createSelector(
  [currentParameters],
  (parameters) => parameters.showStatistics || false
)

export const selectors = {
  STORE_NAME,
  LIST_NAME,

  quizId,
  quizHasScore,
  currentPaper,
  currentNumbering,
  showExpectedAnswers,
  showStatistics
}
