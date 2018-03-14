export const READ_ONLY = 'readonly'
export const MANAGER = 'manager'
export const ADMIN = 'admin'

//if admin or organization manager of the workspace, can also create users here
export const getPermissionLevel = (user, workspace) => {
  if (isAdmin(user)) return ADMIN

  //now we check if we are an organization manager
  if (workspace.organizations.find(organization =>
    !!user.administratedOrganizations.find(admnistratedOrganization => admnistratedOrganization.id === organization.id)
  )) return ADMIN

  //check if workspace_manager now
  const adminRole = workspace.roles.find(role => role.name.indexOf('ROLE_WS_MANAGER') > -1)
  if (user.roles.find(role => role.name === adminRole.name)) return MANAGER

  return READ_ONLY
}

/**
 * Parse the user and check if he has the ROLE_ADMIN
 */
export const isAdmin = (user) => !!user.roles.find(role => role.name === 'ROLE_ADMIN')
