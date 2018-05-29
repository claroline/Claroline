
function hasPermission(action, resourceNode) {
  return !!resourceNode.permissions[action]
}

export {
  hasPermission
}