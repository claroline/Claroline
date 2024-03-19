import {createSelector} from 'reselect'
import get from 'lodash/get'

const STORE_NAME = 'progression'

const store = (state) => get(state, STORE_NAME)

const workspaceEvaluation = createSelector(
  [store],
  (store) => store.workspaceEvaluation
)

const resourceEvaluations = createSelector(
  [store],
  (store) => store.resourceEvaluations || []
)

export const selectors = {
  STORE_NAME,

  workspaceEvaluation,
  resourceEvaluations
}
