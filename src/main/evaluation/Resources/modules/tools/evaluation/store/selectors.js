import {createSelector} from 'reselect'
import get from 'lodash/get'

const STORE_NAME = 'evaluation'

const store = (state) => get(state, STORE_NAME)

const current = createSelector(
  [store],
  (store) => store.current
)

const currentLoaded = createSelector(
  [current],
  (current) => current.loaded
)

const currentWorkspaceEvaluation = createSelector(
  [current],
  (current) => current.workspaceEvaluation
)

const currentResourceEvaluations = createSelector(
  [current],
  (current) => current.resourceEvaluations
)

export const selectors = {
  STORE_NAME,
  store,
  current,
  currentLoaded,
  currentWorkspaceEvaluation,
  currentResourceEvaluations
}
