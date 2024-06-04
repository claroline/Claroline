import {createSelector} from 'reselect'
import get from 'lodash/get'

const STORE_NAME = 'innova_path'

const store = (state) => state[STORE_NAME]

// path
const path = createSelector(
  [store],
  (store) => store.resource
)

const steps = createSelector(
  [path],
  (path) => path.steps || []
)

const empty = createSelector(
  [steps],
  (steps) => 0 === steps.length
)

const showOverview = createSelector(
  [path],
  (path) => get(path, 'overview.display') || false
)

const showEndPage = createSelector(
  [path],
  (path) => get(path, 'end.display') || false
)

// is step navigation enabled ?
const navigationEnabled = createSelector(
  [store],
  (store) => store.navigationEnabled
)

const attempt = createSelector(
  [store],
  (store) => store.attempt
)

const stepsProgression = createSelector(
  [store],
  (store) => store.stepsProgression
)

// evaluation for the required resource of the path
const resourceEvaluations = createSelector(
  [store],
  (store) => store.resourceEvaluations
)

export const selectors = {
  STORE_NAME,

  path,
  steps,
  empty,
  navigationEnabled,
  showOverview,
  showEndPage,
  attempt,
  resourceEvaluations,
  stepsProgression
}
