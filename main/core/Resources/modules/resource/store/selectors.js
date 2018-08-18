import {createSelector} from 'reselect'
import isEmpty from 'lodash/isEmpty'

import {constants} from '#/main/core/resource/constants'

const embedded = (state) => state.embedded

const managed = (state) => state.managed

const loaded = (state) => state.loaded

// lifecycle selectors
const resourceLifecycle = (state) => state.lifecycle

// node selectors
const resourceNode = (state) => state.resourceNode

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

const resourceType = createSelector(
  [meta],
  (meta) => meta.type
)

const mimeType = createSelector(
  [meta],
  (meta) => meta.mimeType
)

// access restrictions selectors
const accessErrors = (state) => !state.accessErrors.dismissed && !isEmpty(state.accessErrors.details) ? state.accessErrors.details : {}

// evaluation selectors
const resourceEvaluation = (state) => state.userEvaluation

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
  embedded,
  managed,
  loaded,
  // lifecycle
  resourceLifecycle,
  // access restrictions
  accessErrors,
  // node
  resourceNode,
  parent,
  meta,
  published,
  resourceType,
  mimeType,
  // evaluation
  resourceEvaluation,
  hasEvaluation,
  evaluationStatus,
  isTerminated,
  isSuccessful
}
