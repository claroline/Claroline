import {route as toolRoute} from '#/main/core/tool/routing'

function route(user, basePath = null) {
  if (basePath) {
    return basePath + '/profile/' + user.publicUrl
  }

  return toolRoute('community') + '/profile/' + user.publicUrl
}

export {
  route
}
