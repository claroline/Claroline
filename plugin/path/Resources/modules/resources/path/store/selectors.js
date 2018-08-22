import {createSelector} from 'reselect'
import get from 'lodash/get'

const STORE_NAME = 'resource'

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
  (path) => {
    return get(path, 'display.showOverview') || false
  }
)

// is step navigation enabled ?
const navigationEnabled = createSelector(
  [resource],
  (resource) => resource.navigationEnabled
)

// summary
const summary = createSelector(
  [resource],
  (resource) => resource.summary
)

const summaryPinned = createSelector(
  [summary],
  (summary) => summary.pinned
)

const summaryOpened = createSelector(
  [summary],
  (summary) => summary.opened
)

// is the current step rendered full width (without opened pinned summary) ?
const fullWidth = createSelector(
  [summaryPinned, summaryOpened],
  (summaryPinned, summaryOpened) => !summaryOpened || !summaryPinned
)

export const selectors = {
  STORE_NAME,
  resource,
  path,
  steps,
  empty,
  summaryPinned,
  summaryOpened,
  fullWidth,
  navigationEnabled,
  showOverview
}
