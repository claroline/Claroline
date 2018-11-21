import {currentUser, isAdmin} from '#/main/app/security'

// TODO : move into a constants file
export const READ_ONLY = 'readonly'
export const MANAGER = 'manager'
export const ADMIN = 'admin'

//if admin or organization manager of the workspace, can also create users here
export const getPermissionLevel = (workspace) => {
  if (isAdmin()) {
    return ADMIN
  }

  const user = currentUser()

  //now we check if we are an organization manager
  if (workspace.organizations.find(organization =>
    !!user.administratedOrganizations.find(admnistratedOrganization => admnistratedOrganization.id === organization.id)
  )) {
    return ADMIN
  }

  //check if workspace_manager now
  const adminRole = workspace.roles.find(role => role.name.indexOf('ROLE_WS_MANAGER') > -1)
  if (user.roles.find(role => role.name === adminRole.name)) {
    return MANAGER
  }

  return READ_ONLY
}
