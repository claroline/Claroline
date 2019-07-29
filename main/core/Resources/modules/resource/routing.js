import {route as toolRoute} from '#/main/core/tool/routing'
import {route as workspaceRoute} from '#/main/core/workspace/routing'

function route(resource) {
  if (resource.workspace) {
    return `${workspaceRoute({id: resource.workspace.autoId}, 'resource_manager')}/${resource.id}`
  }

  return `${toolRoute('resource_manager')}/${resource.id}`
}

export {
  route
}
