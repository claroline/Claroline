import {route as toolRoute} from '#/main/core/tool/routing'

function route(group, basePath = null) {
  if (basePath) {
    return basePath + '/roles/' + group.id
  }

  return toolRoute('community') + '/roles/' + group.id
}

export {
  route
}
