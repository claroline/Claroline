import {route as toolRoute} from '#/main/core/tool/routing'

function route(event, basePath = null) {
  if (!basePath || !basePath.includes('trainings')) {
    return toolRoute('trainings', basePath) + '/events/' + event.id
  }

  return basePath + '/events/' + event.id
}

export {
  route
}
