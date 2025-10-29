import {route as toolRoute} from '#/main/core/tool/routing'

function route(event, basePath = null) {
  return toolRoute('trainings', basePath) + '/events/' + event.id
}

export {
  route
}
