import {createSelector} from 'reselect'
import get from 'lodash/get'

import {selectors as contextSelectors} from '#/main/app/context/store'

const STORE_NAME = 'progression'

const store = (state) => get(state, STORE_NAME)

const assignedSequences = createSelector(
  [store],
  (store) => get(store, 'current.sequences')
)

const currentWorkspaceEvaluation = createSelector(
  [store],
  (store) => get(store, 'current.workspaceEvaluation')
)

const currentResourceEvaluations = createSelector(
  [store],
  (store) => get(store, 'current.resourceEvaluations')
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
  assignedSequences,
  currentWorkspaceEvaluation,
  currentResourceEvaluations,
  hasScore,
  totalScore
}
