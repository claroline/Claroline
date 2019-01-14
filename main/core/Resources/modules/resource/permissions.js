import cloneDeep from 'lodash/cloneDeep'

// TODO : this 3 methods should be moved in a `role` module
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

/**
 * Gets permissions object for a Role.
 *
 * @param {string} roleName
 * @param {Array}  perms
 *
 * @return {object}
 */
const findRolePermissions = (roleName, perms) => {
  const rolePerms = perms.find(perm => perm.name === roleName)
  if (rolePerms) {
    return rolePerms.permissions
  }

  return {}
}

/**
 * Checks if a role has custom permissions.
 * By default a role only have the `open` perm.
 *
 * @param rolePerms
 */
const roleHaveCustomPerms = (rolePerms) => {
  const customPerms = Object.keys(rolePerms)
    .filter(permName => {
      if (-1 === ['open', 'download'].indexOf(permName)) {
        // non standard reading right
        if (rolePerms[permName]) {
          // perm is set
          if (rolePerms[permName] instanceof Array) {
            return 0 < rolePerms[permName].length
          }

          return true
        }
      }

      return false
    })

  return 0 < customPerms.length
}

/**
 * Checks if the resource has custom permissions.
 *
 * @param {Array}  perms
 * @param {object} workspace
 *
 * @returns {boolean}
 */
const hasCustomRules = (perms, workspace = null) => {
  // checks if there are perms for custom roles
  const standard = standardRoles(workspace)
  const customRoles = perms.filter(rolePerm => !isStandardRole(rolePerm.name, workspace))
  if (0 < customRoles.length) {
    return true
  }

  // checks if standard roles have custom perms (aka. other perms than `open`)
  const roleWithCustomRules = standard.filter(roleName => roleHaveCustomPerms(findRolePermissions(roleName, perms)))

  return 0 < roleWithCustomRules.length
}

/**
 * Computes permissions to get a single string representing who have the `open` right.
 *
 * @param {Array}  perms
 * @param {object} workspace
 *
 * @return {string}
 */
const getSimpleAccessRule = (perms, workspace = null) => {
  const anonymous = findRolePermissions(roleAnonymous(), perms)
  if (anonymous.open) {
    return 'all'
  } else {
    const users = findRolePermissions(roleUser(), perms)
    if (users.open) {
      return 'user'
    } else {
      const wsUsers = findRolePermissions(roleWorkspace(workspace), perms)
      if (wsUsers.open) {
        return 'workspace'
      } else {
        return 'admin'
      }
    }
  }
}

const setSimpleAccessRule = (perms, rule, workspace = null) => {
  const updatedPerms = cloneDeep(perms)

  const permsLevel = {
    all: 0,
    user: 1,
    workspace: 2,
    admin: 3
  }

  return updatedPerms.map((rolePerms) => {
    let roleLevel
    if (rolePerms.name === roleAnonymous()) {
      // perms for Anonymous
      roleLevel = 0
    } else if (rolePerms.name === roleUser()) {
      // perms for User
      roleLevel = 1
    } else if (isWorkspaceRole(rolePerms.name, workspace)) {
      // perms for Workspace roles
      roleLevel = 2
    } else {
      // perms for Custom role
      roleLevel = 1
    }

    rolePerms.permissions.open = roleLevel >= permsLevel[rule]

    return rolePerms
  })
}

export {
  roleAnonymous,
  roleUser,
  roleWorkspace,
  findRolePermissions,
  hasCustomRules,
  getSimpleAccessRule,
  setSimpleAccessRule,
  isStandardRole
}
