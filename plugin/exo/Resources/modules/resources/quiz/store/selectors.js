import {createSelector} from 'reselect'

const STORE_NAME = 'ujm_exercise'

/**
 * Gets the whole quiz store object.
 *
 * @type {object}
 */
const resource = (state) => state[STORE_NAME]

/**
 * Gets the full quiz data.
 *
 * @type {object}
 */
const quiz = createSelector(
  [resource],
  (resource) => resource.quiz
)

/**
 * Gets the quiz id.
 *
 * @type {object}
 */
const id = createSelector(
  [quiz],
  (quiz) => quiz.id
)

const steps = createSelector(
  [quiz],
  (quiz) => quiz.steps || []
)

/**
 * Checks if there are items in the quiz.
 *
 * @return {bool}
 */
const empty = createSelector(
  [steps],
  (steps) => -1 === steps.findIndex(step => step.items && 0 < step.items.length)
)

const parameters = createSelector(
  [quiz],
  (quiz) => quiz.parameters || {}
)

const numbering = createSelector(
  [parameters],
  (parameters) => parameters.numbering
)

const showTitles = createSelector(
  [parameters],
  (parameters) => parameters.showTitles || false
)

const showStatistics = createSelector(
  [parameters],
  (parameters) => parameters.showStatistics || false
)

const hasOverview = createSelector(
  [parameters],
  (parameters) => parameters.showOverview || false
)

export const selectors = {
  STORE_NAME,

  resource,
  quiz,
  id,
  empty,
  numbering,
  showStatistics,
  showTitles,
  hasOverview
}
