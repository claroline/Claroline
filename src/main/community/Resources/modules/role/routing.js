import {route as toolRoute} from '#/main/core/tool/routing'

function route(role, basePath = null) {
  if (basePath) {
    return basePath + '/roles/' + role.id
  }

  return toolRoute('community') + '/roles/' + role.id
}

export {
  route
}
