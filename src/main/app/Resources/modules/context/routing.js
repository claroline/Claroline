
function route(contextName, contextId = null, toolName = null) {
  let contextPath = `/${contextName}`
  if (contextId) {
    contextPath += `/${contextId}`
  }

  if (toolName) {
    contextPath += `/${toolName}`
  }

  return contextPath
}

export {
  route
}
