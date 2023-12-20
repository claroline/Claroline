import {route as toolRoute} from '#/main/core/tool/routing'
import get from 'lodash/get'

function route(evaluation, basePath = null) {
  let path
  if (basePath) {
    path = basePath
  } else {
    path = toolRoute('evaluation')
  }

  return `${path}/users/${get(evaluation, 'user.id')}/${get(evaluation, 'workspace.id')}`
}

export {
  route
}
