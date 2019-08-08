import {route as toolRoute} from '#/main/core/tool/routing'
import {route as workspaceRoute} from '#/main/core/workspace/routing'

function route(resource) {    
  if (resource.workspace) {
    return `${workspaceRoute(resource.workspace, 'resource_manager')}/${resource.meta.slug}`
  }

  return `${toolRoute('resource_manager')}/${resource.meta.slug}`
}

export {
  route
}
