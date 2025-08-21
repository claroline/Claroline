import {route as workspaceRoute} from '#/main/core/workspace/routing'

function route(resource) {
  if (resource.workspace) {
    return `${workspaceRoute(resource.workspace, 'resources')}/${resource.slug}`
  }

  return ''
}

export {
  route
}
