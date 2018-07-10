import {createSelector} from 'reselect'
import isEmpty from 'lodash/isEmpty'

import {constants} from '#/main/core/resource/constants'

const STORE_NAME = 'resource'

const resource = (state) => state[STORE_NAME]

const embedded = createSelector(
  [resource],
  (resource) => resource.embedded
)

// lifecycle selectors
const resourceLifecycle = createSelector(
  [resource],
  (resource) => resource.lifecycle
)

// node selectors
const resourceNode = createSelector(
  [resource],
  (resource) => resource.node
)

const parent = createSelector(
  [resourceNode],
  (resourceNode) => resourceNode.parent
)

const meta = createSelector(
  [resourceNode],
  (resourceNode) => resourceNode.meta
)

const published = createSelector(
  [meta],
  (meta) => meta.published
)

// evaluation selectors
const resourceEvaluation = createSelector(
  [resource],
  (resource) => resource.evaluation
)

const evaluationStatus = createSelector(
  [resourceEvaluation],
  (evaluation) => evaluation.status
)

const hasEvaluation = createSelector(
  [resourceEvaluation],
  (evaluation) => !isEmpty(evaluation)
)

const isTerminated = createSelector(
  [evaluationStatus],
  (evaluationStatus) => [
    constants.EVALUATION_STATUS_COMPLETED,
    constants.EVALUATION_STATUS_PASSED,
    constants.EVALUATION_STATUS_FAILED
  ].inArray(evaluationStatus)
)

const isSuccessful = createSelector(
  [evaluationStatus],
  (evaluationStatus) => [
    constants.EVALUATION_STATUS_COMPLETED,
    constants.EVALUATION_STATUS_PASSED
  ].inArray(evaluationStatus)
)

export const selectors = {
  STORE_NAME,
  resource,
  embedded,
  // lifecycle
  resourceLifecycle,
  // node
  resourceNode,
  parent,
  meta,
  published,
  // evaluation
  resourceEvaluation,
  hasEvaluation,
  evaluationStatus,
  isTerminated,
  isSuccessful
}
