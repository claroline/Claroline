import {isAdmin} from '#/main/app/security'

import {constants} from '#/main/core/tools/community/constants'

//if admin or organization manager of the workspace, can also create users here
function getPermissionLevel(user, workspace = null) {
  if (user) {
    if (isAdmin(user)) {
      return constants.ADMIN
    }

    if (workspace) {
      //now we check if we are an organization manager
      if (workspace.organization) {
        if (workspace.organizations.find(organization =>
          !!user.administratedOrganizations.find(administratedOrganization => administratedOrganization.id === organization.id)
        )) {
          return constants.ADMIN
        }
      }

      //check if workspace_manager now
      const adminRole = workspace.roles ? workspace.roles.find(role => role.name.indexOf('ROLE_WS_MANAGER') > -1): false

      if (user.roles && user.roles.find(role => role.name === adminRole.name)) {
        return constants.MANAGER
      }
    }
  }

  return constants.READ_ONLY
}

export {
  getPermissionLevel
}
