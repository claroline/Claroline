import {route as toolRoute} from '#/main/core/tool/routing'

function route(user, basePath = null) {
  if (basePath) {
    return basePath + '/users/' + user.username
  }

  return toolRoute('community') + '/users/' + user.username
}

export {
  route
}
