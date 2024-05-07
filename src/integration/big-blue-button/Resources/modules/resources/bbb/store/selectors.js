import {createSelector} from 'reselect'

import {hasPermission} from '#/main/app/security'

import {selectors as resourceSelect} from '#/main/core/resource/store'

const STORE_NAME = 'claroline_big_blue_button'

const store = (state) => state[STORE_NAME]

const bbb = createSelector(
  [store],
  (store) => store.resource
)

const servers = createSelector(
  [store],
  (store) => store.servers
)

const allowRecords = createSelector(
  [store],
  (store) => store.allowRecords
)

const lastRecording = createSelector(
  [store],
  (store) => store.lastRecording
)

const canStart = createSelector(
  [store],
  (store) => store.canStart
)

const joinStatus = createSelector(
  [store],
  (store) => store.joinStatus
)

const canEdit = createSelector(
  resourceSelect.resourceNode,
  (resourceNode) => hasPermission('edit', resourceNode)
)

export const selectors = {
  STORE_NAME,

  bbb,
  servers,
  allowRecords,
  canEdit,
  canStart,
  joinStatus,
  lastRecording
}
