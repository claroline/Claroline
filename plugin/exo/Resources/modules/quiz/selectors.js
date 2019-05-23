import {createSelector} from 'reselect'

import {currentUser} from '#/main/app/security'

// TODO : there is possible code refactoring with editor/selectors.js

const STORE_NAME = 'resource'

const registered = () => null !== currentUser()

const resource = (state) => state[STORE_NAME]

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

const quiz = createSelector(
  resource,
  (resource) => resource.quiz
)

const statistics = createSelector(
  resource,
  (resource) => resource.statistics
)

const id = createSelector(
  quiz,
  (quiz) => quiz.id
)

const testMode = createSelector(
  resource,
  (resource) => resource.testMode || false
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

// TODO : remove default export and use named one
export default {
  STORE_NAME,
  resource,
  id,
  quiz,
  steps,
  items,
  empty,
  papers,
  statistics,
  registered,
  description,
  meta,
  parameters,
  title,
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
  id,
  quiz,
  steps,
  items,
  empty,
  papers,
  statistics,
  registered,
  description,
  meta,
  parameters,
  title,
  hasOverview,
  testMode,
  quizNumbering,
  papersShowExpectedAnswers,
  papersShowStatistics,
  allPapersStatistics
}

export const selectors = {
  STORE_NAME,
  resource,
  id,
  quiz,
  steps,
  items,
  empty,
  papers,
  statistics,
  registered,
  description,
  meta,
  parameters,
  title,
  hasOverview,
  testMode,
  quizNumbering,
  papersShowExpectedAnswers,
  papersShowStatistics,
  allPapersStatistics
}