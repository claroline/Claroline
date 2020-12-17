import {getApp} from '#/main/app/plugins'

function getWidget(name) {
  return getApp('widgets', name)()
}

export {
  getWidget
}
