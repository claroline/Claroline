import {createSelector} from 'reselect'

import {selectors as resourceSelect} from '#/main/core/resource/store'
import {hasPermission} from '#/main/core/resource/permissions'

const STORE_NAME = 'resource'

const resource = (state) => state[STORE_NAME]

const lesson = createSelector(
  [resource],
  (resource) => resource.lesson
)

const chapter = createSelector(
  [resource],
  (resource) => resource.chapter
)

const mode = createSelector(
  [resource],
  (resource) => resource.mode
)

const tree = createSelector(
  [resource],
  (resource) => resource.tree
)

const treeData = createSelector(
  [tree],
  (tree) => tree.data
)

const treeInvalidated = createSelector(
  [tree],
  (tree) => tree.invalidated
)

const exportPdfEnabled = createSelector(
  [resource],
  (resource) => resource.exportPdfEnabled
)

const canExport = (state) => hasPermission('export', resourceSelect.resourceNode(state)) && exportPdfEnabled(state)

const canEdit = (state) => hasPermission('edit', resourceSelect.resourceNode(state))

export const selectors = {
  STORE_NAME,
  resource,
  lesson,
  mode,
  chapter,
  treeData,
  treeInvalidated,
  canExport,
  canEdit
}
