import {createSelector} from 'reselect'

import {currentUser} from '#/main/core/user/current'
import {selectors as resourceSelect} from '#/main/core/resource/store'
import {hasPermission} from '#/main/core/resource/permissions'

// TODO : there is possible code refactoring with editor/selectors.js

const STORE_NAME = 'resource'

const registered = () => null !== currentUser()

const resource = (state) => state[STORE_NAME]

const noServer = createSelector(
  resource,
  (resource) => resource.noServer
)

const steps = createSelector(
  resource,
  (resource) => resource.steps
)
const items = createSelector(
  resource,
  (resource) => resource.items
)
const papers = createSelector(
  resource,
  (resource) => resource.papers
)

const viewMode = createSelector(
  resource,
  (resource) => resource.viewMode
)

const quiz = createSelector(
  resource,
  (resource) => resource.quiz
)

const id = createSelector(
  quiz,
  (quiz) => quiz.id
)

const testMode = createSelector(
  quiz,
  (quiz) => quiz.testMode || false
)

const quizSteps = createSelector(
  quiz,
  (quiz) => quiz.steps || []
)

const empty = createSelector(
  quizSteps,
  (quizSteps) => quizSteps.length === 0
)

const description = createSelector(
  quiz,
  (quiz) => quiz.description
)

const parameters = createSelector(
  quiz,
  (quiz) => quiz.parameters || {}
)

const title = createSelector(
  quiz,
  (quiz) => quiz.title
)

const meta = createSelector(
  quiz,
  (quiz) => quiz.meta || {}
)

const hasUserPapers = createSelector(
  meta,
  (meta) => meta.userPaperCount > 0
)

const paperCount = createSelector(
  meta,
  (meta) => meta.paperCount || 0
)

const hasPapers = createSelector(
  [paperCount, papers],
  (paperCount, papers) => paperCount > 0 || (papers.papers && papers.papers.length > 0)
)

const noItems = createSelector(
  [steps, items],
  (steps, items) => Object.keys(steps).length === 1 && Object.keys(items).length === 0
)
const firstStepId = createSelector(
  quizSteps,
  (quizSteps) => quizSteps[0]
)

const hasOverview = createSelector(
  parameters,
  (parameters) => parameters.showOverview || false
)
const papersShowExpectedAnswers = createSelector(
  parameters,
  (parameters) => parameters.showFullCorrection || false
)
const papersShowStatistics = createSelector(
  parameters,
  (parameters) => parameters.showStatistics
)
const allPapersStatistics = createSelector(
  parameters,
  (parameters) => parameters.allPapersStatistics
)
const quizNumbering = createSelector(
  parameters,
  (parameters) => parameters.numbering
)

const papersAdmin = createSelector(
  [resourceSelect.resourceNode],
  (resourceNode) => hasPermission('manage_papers', resourceNode)
)

const docimologyAdmin = createSelector(
  [resourceSelect.resourceNode],
  (resourceNode) => hasPermission('view_docimology', resourceNode)
)

// TODO : remove default export and use named one
export default {
  STORE_NAME,
  resource,
  noServer,
  id,
  quiz,
  steps,
  items,
  empty,
  papers,
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
  noItems,
  firstStepId,
  hasOverview,
  testMode,
  quizNumbering,
  papersShowExpectedAnswers,
  papersShowStatistics,
  allPapersStatistics
}

export const select = {
  STORE_NAME,
  resource,
  noServer,
  id,
  quiz,
  steps,
  items,
  empty,
  papers,
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
  noItems,
  firstStepId,
  hasOverview,
  testMode,
  quizNumbering,
  papersShowExpectedAnswers,
  papersShowStatistics,
  allPapersStatistics
}
