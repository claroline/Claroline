import {route as toolRoute} from '#/main/core/tool/routing'
import {route as workspaceRoute} from '#/main/core/workspace/routing'

function route(resource) {
  if (resource.workspace) {
    return `${workspaceRoute(resource.workspace, 'resources')}/${resource.slug}`
  }

  return `${toolRoute('resources')}/${resource.slug}`
}

export {
  route
}
