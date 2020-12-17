import {createSelector} from 'reselect'
import get from 'lodash/get'

import {selectors as baseSelectors} from '#/plugin/analytics/tools/dashboard/store/selectors'

const STORE_NAME = baseSelectors.STORE_NAME + '.progression'

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

const requirements = createSelector(
  [store],
  (store) => store.requirements
)

const currentRequirements = createSelector(
  [requirements],
  (requirements) => requirements.current
)

export const selectors = {
  STORE_NAME,

  current,
  currentLoaded,
  currentWorkspaceEvaluation,
  currentResourceEvaluations,

  currentRequirements
}
