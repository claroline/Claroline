import {route as toolRoute} from '#/main/core/tool/routing'

function route(workspace, toolName = null) {
  if (toolName) {
    return toolRoute('workspaces')+`/open/${workspace.id}/${toolName}`
  }

  return toolRoute('workspaces')+`/open/${workspace.id}`
}

export {
  route
}
