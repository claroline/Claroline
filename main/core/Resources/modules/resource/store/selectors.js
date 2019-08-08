import {createSelector} from 'reselect'
import isEmpty from 'lodash/isEmpty'

import {selectors as toolSelectors} from '#/main/core/tool/store/selectors'

import {constants} from '#/main/core/resource/constants'

const STORE_NAME = 'resource'

const store = (state) => state[STORE_NAME]

const id = createSelector(
  [store],
  (store) => store.id
)

const resourceNode = createSelector(
  [store],
  (store) => store.resourceNode || {}
)

const basePath = toolSelectors.path

const path = createSelector(
  [basePath, resourceNode],
  (basePath, resourceNode) => {
    return basePath + '/' + (resourceNode.meta ? resourceNode.meta.slug: null)
  }
)

const embedded = createSelector(
  [store],
  (store) => store.embedded
)
const showHeader = createSelector(
  [store],
  (store) => store.showHeader
)

const managed = createSelector(
  [store],
  (store) => store.managed
)

const nodeLoaded = createSelector(
  [store],
  (store) => store.nodeLoaded
)

const loaded = createSelector(
  [store],
  (store) => store.loaded
)

// lifecycle selectors
const resourceLifecycle = createSelector(
  [store],
  (store) => store.lifecycle
)

// node selectors
const parent = createSelector(
  [resourceNode],
  (resourceNode) => resourceNode.parent
)

const workspace = createSelector(
  [resourceNode],
  (resourceNode) => resourceNode.workspace
)

const workspaceId = createSelector(
  [workspace],
  (workspace) => workspace.autoId
)

const meta = createSelector(
  [resourceNode],
  (resourceNode) => resourceNode.meta || {}
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
const accessErrors = createSelector(
  [store],
  (store) => !store.accessErrors.dismissed && !isEmpty(store.accessErrors.details) ? store.accessErrors.details : {}
)

const serverErrors = createSelector(
  [store],
  (store) => !isEmpty(store.serverErrors) ? store.serverErrors : []
)

// evaluation selectors
const resourceEvaluation = createSelector(
  [store],
  (store) => store.userEvaluation
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

  path,
  basePath,
  embedded,
  showHeader,
  managed,
  nodeLoaded,
  loaded,
  // lifecycle
  resourceLifecycle,
  // access restrictions
  accessErrors,
  serverErrors,
  // node
  resourceNode,
  id,
  workspace,
  workspaceId,
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
