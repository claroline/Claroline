
function route(toolName) {
  if (toolName) {
    return `/desktop/${toolName}`
  }

  return '/desktop'
}

export {
  route
}
