import cloneDeep from 'lodash/cloneDeep'

import {
  roleAnonymous,
  roleUser,
  roleWorkspace,
  hasCustomRoles,
  isWorkspaceRole,
  isStandardRole
} from '#/main/community/permissions'

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
  if (hasCustomRoles(perms, workspace)) {
    return true
  }

  // checks if standard roles have custom perms (aka. other perms than `open`)
  const standardPerms = perms.filter(rolePerm => isStandardRole(rolePerm.name, workspace) && (!workspace || rolePerm.name !== roleWorkspace(workspace, true)))
  const roleWithCustomRules = standardPerms.filter(standardPerm => roleHaveCustomPerms(standardPerm.permissions))

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
  }

  const users = findRolePermissions(roleUser(), perms)
  if (users.open) {
    return 'user'
  }

  if (workspace) {
    const wsRoles = perms
      // find perms for all non manager roles of the workspace
      .filter(perm => perm.workspace && perm.name !== roleWorkspace(workspace, true) && perm.workspace.id === workspace.id)
      // checks if at least one as open perm
      .filter(perm => perm.permissions && perm.permissions.open)

    if (0 !== wsRoles.length) {
      return 'workspace'
    }
  }

  return 'admin'
}

const setSimpleAccessRule = (perms, rule, workspace = null) => {
  const updatedPerms = cloneDeep(perms)

  const permsLevel = {
    all: 0,
    user: 1,
    workspace: 2,
    admin: 3
  }

  return updatedPerms
    .map((rolePerms) => {
      if (!workspace || rolePerms.name === roleWorkspace(workspace, true)) {
        // do not update workspace manager role
        return rolePerms
      }

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
  hasCustomRules,
  getSimpleAccessRule,
  setSimpleAccessRule
}
