const workspace = (state) => state.workspace
const restrictions = (state) => state.restrictions
const collaboratorRole = (state) => state.workspace.roles.find(role => role.translationKey === 'collaborator')

export const select = {
  workspace,
  restrictions,
  collaboratorRole
}
