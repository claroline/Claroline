import {createSelector} from 'reselect'

import {selectors as toolSelectors} from '#/main/core/tool/store/selectors'

const STORE_NAME = 'claroline_planned_notification_tool'

const store = (state) => state[STORE_NAME]

const canEdit = createSelector(
  [store],
  (store) => store.canEdit
)
const workspace = (state) => toolSelectors.contextData(state)

const workspaceRolesChoices = createSelector(
  [workspace],
  (workspace) => {
    const roles = {}
    workspace.roles.forEach(r => roles[r.id] = r.translationKey)

    return roles
  }
)

export const selectors = {
  STORE_NAME,

  canEdit,
  workspace,
  workspaceRolesChoices
}