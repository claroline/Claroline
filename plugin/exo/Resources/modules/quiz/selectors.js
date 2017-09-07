import {createSelector} from 'reselect'

import {select as resourceSelect} from '#/main/core/layout/resource/selectors'

// TODO : use reselect
// TODO : there is possible code refactoring with editor/selectors.js

const isLoading = state => state.currentRequests > 0
const alerts = state => state.alerts
const empty = state => state.quiz.steps.length === 0
const quiz = state => state.quiz
const steps = state => state.steps
const items = state => state.items
const id = state => state.quiz.id
const description = state => state.quiz.description
const parameters = state => state.quiz.parameters
const title = state => state.quiz.title
const meta = state => state.quiz.meta
const viewMode = state => state.viewMode
const hasPapers = state => state.quiz.meta.paperCount > 0 || (state.papers.papers && state.papers.papers.length > 0)
const hasUserPapers = state => state.quiz.meta.userPaperCount > 0

const registered = state => state.quiz.meta.registered
const saveEnabled = state => !state.editor.saved && !state.editor.saving
const editorOpened = state => state.editor.opened
const noItems = state =>
  Object.keys(state.quiz.steps).length === 1 && Object.keys(state.items).length === 0
const firstStepId = state => state.quiz.steps[0]
const hasOverview = state => state.quiz.parameters.showOverview
const testMode = state => state.quiz.testMode
const papersShowExpectedAnswers = state => state.quiz.parameters.showFullCorrection
const papersShowStatistics = state => state.quiz.parameters.showStatistics
const allPapersStatistics = state => state.quiz.parameters.allPapersStatistics

const quizNumbering = createSelector(
  parameters,
  (parameters) => parameters.numbering
)

const papersAdmin = createSelector(
  [resourceSelect.currentRights],
  (currentRights) => !!currentRights.manage_papers
)

const docimologyAdmin = createSelector(
  [resourceSelect.currentRights],
  (currentRights) => !!currentRights.view_docimology
)

export default {
  id,
  quiz,
  steps,
  items,
  empty,
  hasPapers,
  hasUserPapers,
  papersAdmin,
  docimologyAdmin,
  registered,
  description,
  meta,
  parameters,
  title,
  viewMode,
  isLoading,
  alerts,
  saveEnabled,
  editorOpened,
  noItems,
  firstStepId,
  hasOverview,
  testMode,
  quizNumbering,
  papersShowExpectedAnswers,
  papersShowStatistics,
  allPapersStatistics
}
