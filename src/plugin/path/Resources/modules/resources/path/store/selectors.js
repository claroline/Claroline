import {createSelector} from 'reselect'
import get from 'lodash/get'

const STORE_NAME = 'innova_path'

const resource = (state) => state[STORE_NAME]

// path
const path = createSelector(
  [resource],
  (resource) => resource.path
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

// is step navigation enabled ?
const navigationEnabled = createSelector(
  [resource],
  (resource) => resource.navigationEnabled
)

const attempt = createSelector(
  [resource],
  (resource) => resource.attempt
)

const stepsProgression = createSelector(
  [resource],
  (resource) => resource.stepsProgression
)

// evaluation for the required resource of the path
const resourceEvaluations = createSelector(
  [resource],
  (resource) => resource.resourceEvaluations
)

export const selectors = {
  STORE_NAME,
  resource,
  path,
  steps,
  empty,
  navigationEnabled,
  showOverview,
  attempt,
  resourceEvaluations,
  stepsProgression
}
