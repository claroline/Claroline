function hasPermission(action, user) {
  return !!user.permissions[action]
}

export {
  hasPermission
}