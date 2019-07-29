import {isAdmin} from '#/main/app/security'

import {constants} from '#/main/core/tools/users/constants'

//if admin or organization manager of the workspace, can also create users here
function getPermissionLevel(workspace, user) {
  if (user) {
    if (isAdmin(user)) {
      return constants.ADMIN
    }

    //now we check if we are an organization manager
    if (workspace.organizations.find(organization =>
      !!user.administratedOrganizations.find(administratedOrganization => administratedOrganization.id === organization.id)
    )) {
      return constants.ADMIN
    }

    //check if workspace_manager now
    const adminRole = workspace.roles.find(role => role.name.indexOf('ROLE_WS_MANAGER') > -1)

    if (user.roles.find(role => role.name === adminRole.name)) {
      return constants.MANAGER
    }
  }

  return constants.READ_ONLY
}

export {
  getPermissionLevel
}
