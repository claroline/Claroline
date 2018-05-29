// TODO : migrate rights
function hasPermission(action, user) {
  return !!user.rights.current[action]
}

export {
  hasPermission
}