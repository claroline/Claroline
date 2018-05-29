
function hasPermission(action, workspace) {
  return !!workspace.permissions[action]
}

export {
  hasPermission
}