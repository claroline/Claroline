
const roleAnonymous = () => 'ROLE_ANONYMOUS'
const roleUser = () => 'ROLE_USER'
const roleWorkspace = (workspace, admin = false) => (admin ? 'ROLE_WS_MANAGER_':'ROLE_WS_COLLABORATOR_')+workspace.id

/**
 * Gets standard roles that have permissions on the ResourceNode.
 *
 * @param workspace
 *
 * @returns {Array}
 */
const standardRoles = (workspace = null) => {
  const roles = [roleAnonymous(), roleUser()]
  if (workspace) {
    roles.push(roleWorkspace(workspace))
  }

  return roles
}

const hasCustomRoles = (perms, workspace = null) => {
  // checks if there are perms for custom roles
  const customRoles = perms.filter(rolePerm => !isStandardRole(rolePerm.name, workspace))

  return 0 < customRoles.length
}

const isWorkspaceRole = (roleName, workspace) => roleName.endsWith(workspace.id)

const isStandardRole = (roleName, workspace = null) => {
  if (roleAnonymous() === roleName || roleUser() === roleName) {
    // it's a platform role
    return true
  }

  if (workspace && isWorkspaceRole(roleName, workspace)) {
    // it's a role of the resource Workspace
    return true
  }

  return false
}


export {
  roleAnonymous,
  roleUser,
  roleWorkspace,
  standardRoles,
  hasCustomRoles,
  isWorkspaceRole,
  isStandardRole
}
