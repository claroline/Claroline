import {createSelector} from 'reselect'
import get from 'lodash/get'

import {selectors as contextSelectors} from '#/main/app/context/store'

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

const totalScore = createSelector(
  [contextSelectors.data],
  (contextData) => get(contextData, 'evaluation.scoreTotal', null)
)

const hasScore = createSelector(
  [contextSelectors.type, totalScore],
  (contextType, contextScore) => 'desktop' === contextType || !!contextScore
)

export const selectors = {
  STORE_NAME,
  store,
  current,
  currentLoaded,
  currentWorkspaceEvaluation,
  currentResourceEvaluations,
  hasScore,
  totalScore
}
