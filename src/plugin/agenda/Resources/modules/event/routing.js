import {route as toolRoute} from '#/main/core/tool/routing'

function route(event, basePath = null) {
  return toolRoute('agenda', basePath) + '/events/' + event.id
}

export {
  route
}
