/**
 * Checks if the current user has the permission `permission` on `object`.
 *
 * @param {string} permission - the permission to check
 * @param {object} object     - the target object (can be any object exposing a `permissions` sub-object)
 *
 * @return {boolean}
 */
function hasPermission(permission, object) {
  return !!object.permissions[permission]
}

export {
  hasPermission
}
