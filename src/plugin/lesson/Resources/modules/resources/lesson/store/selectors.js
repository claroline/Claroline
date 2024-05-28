import {createSelector} from 'reselect'
import get from 'lodash/get'

import {selectors as resourceSelect} from '#/main/core/resource/store'
import {hasPermission} from '#/main/app/security'

const STORE_NAME = 'icap_lesson'
const LIST_NAME = STORE_NAME + '.chapters'

const CHAPTER_EDIT_FORM_NAME = STORE_NAME + '.chapter_form'

const store = (state) => state[STORE_NAME]

const lesson = createSelector(
  [store],
  (store) => store.resource
)

const chapter = createSelector(
  [store],
  (store) => store.chapter
)

const root = createSelector(
  [store],
  (store) => store.root
)

const tree = createSelector(
  [store],
  (store) => store.tree
)

const treeData = createSelector(
  [tree],
  (tree) => tree.data
)

const treeInvalidated = createSelector(
  [tree],
  (tree) => tree.invalidated
)

const canExport = (state) => hasPermission('export', resourceSelect.resourceNode(state))

const canEdit = (state) => hasPermission('edit', resourceSelect.resourceNode(state))

const showOverview = createSelector(
  [lesson],
  (lesson) => get(lesson, 'display.showOverview') || false
)

export const selectors = {
  STORE_NAME,
  LIST_NAME,
  CHAPTER_EDIT_FORM_NAME,

  lesson,
  chapter,
  root,
  treeData,
  treeInvalidated,
  canExport,
  canEdit,
  showOverview
}
