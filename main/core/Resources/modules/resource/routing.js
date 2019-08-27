import {route as toolRoute} from '#/main/core/tool/routing'
import {route as workspaceRoute} from '#/main/core/workspace/routing'

function route(resource) {    
  if (resource.workspace) {
    return `${workspaceRoute(resource.workspace, 'resource_manager')}/${resource.slug}`
  }

  return `${toolRoute('resource_manager')}/${resource.slug}`
}

export {
  route
}
