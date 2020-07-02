import {createSelector} from 'reselect'

import {hasPermission} from '#/main/app/security'

import {selectors as resourceSelect} from '#/main/core/resource/store'

const STORE_NAME = 'claroline_big_blue_button'

const resource = (state) => state[STORE_NAME]

const bbb = createSelector(
  [resource],
  (resource) => resource.bbb
)

const allowRecords = createSelector(
  [resource],
  (resource) => resource.allowRecords
)

const lastRecording = createSelector(
  [resource],
  (resource) => resource.lastRecording
)

const canStart = createSelector(
  [resource],
  (resource) => resource.canStart
)

const joinStatus = createSelector(
  [resource],
  (resource) => resource.joinStatus
)

const canEdit = createSelector(
  resourceSelect.resourceNode,
  (resourceNode) => hasPermission('edit', resourceNode)
)

export const selectors = {
  STORE_NAME,

  resource,
  bbb,
  allowRecords,
  canEdit,
  canStart,
  joinStatus,
  lastRecording
}
