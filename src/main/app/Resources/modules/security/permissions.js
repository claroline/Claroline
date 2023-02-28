import get from 'lodash/get'

/**
 * Checks if the user has the specified role.
 *
 * @param {string} roleName
 * @param {object} user
 *
 * @return {boolean}
 */
function hasRole(roleName, user) {
  if (user && user.roles) {
    return -1 !== user.roles.findIndex(role => role.name === roleName)
  }

  return false
}

/**
 * Checks if a user is an administrator.
 *
 * @param {object} user
 */
function isAdmin(user) {
  return hasRole('ROLE_ADMIN', user)
}

/**
 * Checks if the current user has the permission `permission` on `object`.
 *
 * @param {string} permission - the permission to check
 * @param {object} object     - the target object (must be any object exposing a `permissions` sub-object)
 *
 * @return {boolean}
 */
function hasPermission(permission, object) {
  return get(object.permissions, permission, false)
}

export {
  hasRole,
  isAdmin,
  hasPermission
}
