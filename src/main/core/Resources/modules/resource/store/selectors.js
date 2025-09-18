import {createSelector} from 'reselect'
import isEmpty from 'lodash/isEmpty'
import get from 'lodash/get'

import {hasPermission} from '#/main/app/security'
import {selectors as toolSelectors} from '#/main/core/tool/store/selectors'

import {constants as baseConstants} from '#/main/evaluation/constants'
import {supportAttempts, supportEvaluation, supportScore} from '#/main/core/resource/utils'

const STORE_NAME = 'resource'
const EDITOR_NAME = 'resourceEditor'

const store = (state) => state[STORE_NAME]

const slug = createSelector(
  [store],
  (store) => store.slug
)

const resource = createSelector(
  [store],
  (store) => store.resource
)

const resourceNode = createSelector(
  [store],
  (store) => store.resourceNode || {}
)

const id = createSelector(
  [resourceNode],
  (resourceNode) => resourceNode.id
)

const name = createSelector(
  [resourceNode],
  (resourceNode) => resourceNode.name
)

const basePath = toolSelectors.path

const path = createSelector(
  [basePath, resourceNode],
  (basePath, resourceNode) => {
    return basePath + '/' + (resourceNode.slug || '')
  }
)

const isRoot = createSelector(
  [resourceNode],
  (resourceNode) => isEmpty(resourceNode.parent)
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
  (workspace) => workspace.id
)

const meta = createSelector(
  [resourceNode],
  (resourceNode) => resourceNode.meta || {}
)

const published = createSelector(
  [meta],
  (meta) => meta.published
)

const downloadable = createSelector(
  [meta],
  (meta) => meta.downloadable
)

const resourceType = createSelector(
  [meta],
  (meta) => meta.type
)

const mimeType = createSelector(
  [meta],
  (meta) => meta.mimeType
)

const canEdit = createSelector(
  [resourceNode],
  (resourceNode) => hasPermission('edit', resourceNode)
)

const canFollow = createSelector(
  [resourceNode],
  (resourceNode) => hasPermission('follow', resourceNode)
)

// access restrictions selectors
const accessErrors = createSelector(
  [store],
  (store) => !store.accessErrors.dismissed && !isEmpty(store.accessErrors.details) ? store.accessErrors.details : {}
)

const estimatedDuration = createSelector(
  [resourceNode],
  (resourceNode) => get(resourceNode, 'evaluation.estimateDuration')
)

const hasEvaluation = createSelector(
  [resourceNode],
  (resourceNode) => !isEmpty(resourceNode) && supportEvaluation(resourceNode)
)

const totalScore = createSelector(
  [resourceNode],
  (sequence) => get(sequence, 'evaluation.scoreTotal', null)
)

const hasScore = createSelector(
  [resourceNode, totalScore],
  (resourceNode, totalScore) => supportScore(resourceNode) && !!totalScore
)

const hasAttempts = createSelector(
  [resourceNode],
  (resourceNode) => supportAttempts(resourceNode)
)

// evaluation selectors
const userEvaluation = createSelector(
  [store],
  (store) => store.userEvaluation
)

/**
 * @deprecated use userEvaluation()
 */
const resourceEvaluation = userEvaluation

const evaluationStatus = createSelector(
  [userEvaluation],
  (evaluation) => evaluation ? evaluation.status : null
)

const isTerminated = createSelector(
  [evaluationStatus],
  (evaluationStatus) => [
    baseConstants.EVALUATION_STATUS_COMPLETED,
    baseConstants.EVALUATION_STATUS_PASSED,
    baseConstants.EVALUATION_STATUS_FAILED
  ].inArray(evaluationStatus)
)

export const selectors = {
  STORE_NAME,
  EDITOR_NAME,

  path,
  basePath,
  isRoot,
  embedded,
  showHeader,
  managed,
  loaded,
  notFound,
  canEdit,
  canFollow,
  resourceLifecycle,
  accessErrors,
  resourceNode,
  resource,
  slug,
  id,
  name,
  workspace,
  workspaceId,
  parent,
  meta,
  published,
  downloadable,
  resourceType,
  mimeType,
  // evaluation
  estimatedDuration,
  userEvaluation,
  resourceEvaluation,
  hasEvaluation,
  hasScore,
  hasAttempts,
  totalScore,
  evaluationStatus,
  isTerminated
}
