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

const evaluation = createSelector(
  [store],
  (store) => store.evaluation
)

const totalScore = createSelector(
  [evaluation],
  (evaluation) => get(evaluation, 'scoreTotal', null)
)

const hasScore = createSelector(
  [evaluation],
  (evaluation) =>  get(evaluation, 'score', null)
)

export const selectors = {
  STORE_NAME,
  store,
  assignedSequences,
  currentWorkspaceEvaluation,
  currentResourceEvaluations,
  evaluation,
  hasScore,
  totalScore
}
