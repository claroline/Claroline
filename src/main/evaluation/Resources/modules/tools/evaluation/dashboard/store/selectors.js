import {createSelector} from 'reselect'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {selectors as toolSelectors} from '#/main/core/tool/store/selectors'

const STORE_NAME = 'progressionDashboard'

const workspace = toolSelectors.contextData

const archived = createSelector(
  [workspace],
  (workspace) => get(workspace, 'meta.archived', false)
)

const totalScore = createSelector(
  [workspace],
  (sequence) => get(sequence, 'evaluation.scoreTotal', null)
)

const hasEvaluation = createSelector(
  [],
  () => true
)

const hasScore = createSelector(
  [totalScore],
  (totalScore) => !!totalScore
)

const successCondition = createSelector(
  [workspace],
  (sequence) => get(sequence, 'evaluation.successCondition', null)
)

const successScore = createSelector(
  [workspace],
  (sequence) => get(sequence, 'evaluation.successCondition.score', null)
)

const countSuccessCondition = createSelector(
  [successCondition],
  (successCondition) => {
    if (isEmpty(successCondition)) {
      return 0
    }

    return Object.keys(successCondition).length
  }
)

const hasSuccessCondition = createSelector(
  [successCondition],
  (successCondition) => !!successCondition
)

export const selectors = {
  STORE_NAME,
  workspace,
  archived,
  hasEvaluation,
  hasScore,
  totalScore,
  hasSuccessCondition,
  countSuccessCondition,
  successCondition,
  successScore
}
