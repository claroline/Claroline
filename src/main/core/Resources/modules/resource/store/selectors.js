import {createSelector} from 'reselect'
import isEmpty from 'lodash/isEmpty'

import {selectors as toolSelectors} from '#/main/core/tool/store/selectors'

import {constants as baseConstants} from '#/main/evaluation/constants'

const STORE_NAME = 'resource'

const store = (state) => state[STORE_NAME]

const slug = createSelector(
  [store],
  (store) => store.slug
)

const resourceNode = createSelector(
  [store],
  (store) => store.resourceNode || {}
)

const id = createSelector(
  [resourceNode],
  (resourceNode) => resourceNode.id
)

const basePath = toolSelectors.path

const path = createSelector(
  [basePath, resourceNode],
  (basePath, resourceNode) => {
    return basePath + '/' + (resourceNode.meta ? resourceNode.slug: null)
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

const loaded = createSelector(
  [store],
  (store) => store.loaded
)

const notFound = createSelector(
  [store],
  (store) => store.notFound
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
    baseConstants.EVALUATION_STATUS_COMPLETED,
    baseConstants.EVALUATION_STATUS_PASSED,
    baseConstants.EVALUATION_STATUS_FAILED
  ].inArray(evaluationStatus)
)

const isSuccessful = createSelector(
  [evaluationStatus],
  (evaluationStatus) => [
    baseConstants.EVALUATION_STATUS_COMPLETED,
    baseConstants.EVALUATION_STATUS_PASSED
  ].inArray(evaluationStatus)
)

export const selectors = {
  STORE_NAME,

  path,
  basePath,
  embedded,
  showHeader,
  managed,
  loaded,
  notFound,
  // lifecycle
  resourceLifecycle,
  // access restrictions
  accessErrors,
  // node
  resourceNode,
  slug,
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
