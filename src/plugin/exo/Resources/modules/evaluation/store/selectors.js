import {createSelector} from 'reselect'
import get from 'lodash/get'

const STORE_NAME = 'quizAttempt'

const store = (state) => state[STORE_NAME]

const stats = createSelector(
  [store],
  (state) => state.stats
)

const currentPaper = createSelector(
  [store],
  (papersState) => papersState.paper
)

const quizId = createSelector(
  [currentPaper],
  (currentPaper) => get(currentPaper, 'structure.id')
)

const quizHasScore = createSelector(
  [currentPaper],
  (currentPaper) => !!get(currentPaper, 'structure.score') && 'none' !== get(currentPaper, 'structure.score.type')
)

const currentSteps = createSelector(
  [currentPaper],
  (currentPaper) => get(currentPaper, 'structure.steps') || []
)

const currentParameters = createSelector(
  [currentPaper],
  (currentPaper) => get(currentPaper, 'structure.parameters') || {}
)

const currentNumbering = createSelector(
  [currentParameters],
  (currentParameters) => currentParameters.numbering
)

const currentQuestionNumbering = createSelector(
  [currentParameters],
  (currentParameters) => currentParameters.questionNumbering
)

const showTitles = createSelector(
  [currentParameters],
  // managing undefined is for retro-compatibility (I don't want to migrate all papers to set the parameter)
  (currentParameters) => currentParameters.showTitles === undefined ? true : currentParameters.showTitles
)

const showQuestionTitles = createSelector(
  [currentParameters],
  // managing undefined is for retro-compatibility (I don't want to migrate all papers to set the parameter)
  (currentParameters) => currentParameters.showQuestionTitles === undefined ? true : currentParameters.showQuestionTitles
)

const showExpectedAnswers = createSelector(
  [currentParameters],
  (parameters) => parameters.showFullCorrection || false
)

const showStatistics = createSelector(
  [currentParameters],
  (parameters) => parameters.showStatistics || false
)

const answers = createSelector(
  [currentPaper],
  (currentPaper) => currentPaper ? currentPaper.answers : null
)

export const selectors = {
  STORE_NAME,

  quizId,
  quizHasScore,
  currentPaper,
  stats,
  currentSteps,
  currentNumbering,
  showTitles,
  showExpectedAnswers,
  showStatistics,
  currentQuestionNumbering,
  showQuestionTitles,
  answers
}
