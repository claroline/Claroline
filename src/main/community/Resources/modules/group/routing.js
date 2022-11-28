import {route as toolRoute} from '#/main/core/tool/routing'

function route(group, basePath = null) {
  if (basePath) {
    return basePath + '/groups/' + group.id
  }

  return toolRoute('community') + '/groups/' + group.id
}

export {
  route
}
