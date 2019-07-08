import {isAdmin} from '#/main/app/security'

// TODO : move into a constants file
const READ_ONLY = 'readonly'
const MANAGER = 'manager'
const ADMIN = 'admin'

//if admin or organization manager of the workspace, can also create users here
function getPermissionLevel(workspace, user) {
  if (user) {
    if (isAdmin(user)) {
      return ADMIN
    }

    //now we check if we are an organization manager
    if (workspace.organizations.find(organization =>
        !!user.administratedOrganizations.find(administratedOrganization => administratedOrganization.id === organization.id)
      )) {
      return ADMIN
    }

    //check if workspace_manager now
    const adminRole = workspace.roles.find(role => role.name.indexOf('ROLE_WS_MANAGER') > -1)
    if (user.roles.find(role => role.name === adminRole.name)) {
      return MANAGER
    }
  }

  return READ_ONLY
}

export {
  READ_ONLY,
  MANAGER,
  ADMIN,
  getPermissionLevel
}
