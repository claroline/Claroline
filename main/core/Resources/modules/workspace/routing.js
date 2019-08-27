import {route as toolRoute} from '#/main/core/tool/routing'

function route(workspace, toolName = null) {
  if (toolName) {
    return toolRoute('workspaces')+`/open/${workspace.slug}/${toolName}`
  }

  return toolRoute('workspaces')+`/open/${workspace.slug}`
}

export {
  route
}
