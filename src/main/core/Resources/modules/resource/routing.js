import {route as toolRoute} from '#/main/core/tool/routing'
import {route as workspaceRoute} from '#/main/core/workspace/routing'

function route(resource, basePath = null) {
  if (basePath) {
    return `${basePath}/${resource.slug}`
  }

  if (resource.workspace) {
    return `${workspaceRoute(resource.workspace, 'resources')}/${resource.slug}`
  }

  return `${toolRoute('resources')}/${resource.slug}`
}

export {
  route
}
