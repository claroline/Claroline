/**
 * Checks if a user is an administrator.
 *
 * @param {object} user
 */
function isAdmin(user) {
  if (user) {
    return !!user.roles.find(role => role.name === 'ROLE_ADMIN')
  }

  return false
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
  return !!object.permissions[permission]
}

export {
  isAdmin,
  hasPermission
}
