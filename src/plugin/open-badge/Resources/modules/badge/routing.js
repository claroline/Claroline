import {route as toolRoute} from '#/main/core/tool/routing'

function route(badge, basePath = null) {
  if (basePath) {
    return basePath + '/' + badge.id
  }

  return toolRoute('badges') + '/' + badge.id
}

export {
  route
}
