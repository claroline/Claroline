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
  (path) => get(path, 'display.showOverview') || false
)

// is step navigation enabled ?
const navigationEnabled = createSelector(
  [resource],
  (resource) => resource.navigationEnabled
)

export const selectors = {
  STORE_NAME,
  resource,
  path,
  steps,
  empty,
  navigationEnabled,
  showOverview
}
