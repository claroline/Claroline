import {createSelector} from 'reselect'

const canEdit = state => state.canEdit
const workspace = state => state.workspace

const workspaceRolesChoices = createSelector(
  [workspace],
  (workspace) => {
    const roles = {}
    workspace.roles.forEach(r => roles[r.id] = r.translationKey)

    return roles
  }
)

export const select = {
  canEdit,
  workspace,
  workspaceRolesChoices
}