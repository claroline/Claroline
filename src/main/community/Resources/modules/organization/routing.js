import {route as toolRoute} from '#/main/core/tool/routing'

function route(organization, basePath = null) {
  if (basePath) {
    return basePath + '/organizations/' + organization.id
  }

  return toolRoute('community') + '/organizations/' + organization.id
}

export {
  route
}
