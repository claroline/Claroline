
function route(workspace, toolName = null) {
  if (toolName) {
    return `/workspace/${workspace.slug}/${toolName}`
  }

  return `/workspace/${workspace.slug}`
}

export {
  route
}
