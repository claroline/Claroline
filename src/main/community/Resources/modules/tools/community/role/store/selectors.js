import {createSelector} from 'reselect'
import get from 'lodash/get'

import {hasPermission} from '#/main/app/security'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {selectors as baseSelectors} from '#/main/community/tools/community/store/selectors'
import {selectors as formSelectors} from '#/main/app/content/form/store'

const STORE_NAME = baseSelectors.STORE_NAME + '.roles'

const LIST_NAME = STORE_NAME + '.list'
const FORM_NAME = STORE_NAME + '.current'

const current = (state) => get(state, FORM_NAME)

const currentId = (state) => formSelectors.data(formSelectors.form(state, FORM_NAME)).id || null

const canCreate = createSelector(
  [toolSelectors.toolData],
  (tool) => hasPermission('edit', tool)
)

const desktopRights = createSelector(
  [current],
  (current) => current.desktopRights
)

const administrationRights = createSelector(
  [current],
  (current) => current.administrationRights
)

const workspaceRights = createSelector(
  [current],
  (current) => current.workspaceRights
)

export const selectors = {
  STORE_NAME,
  LIST_NAME,
  FORM_NAME,

  currentId,
  canCreate,
  desktopRights,
  administrationRights,
  workspaceRights
}
