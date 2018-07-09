import {getApp} from '#/main/app/plugins'

function getWidget(name) {
  return getApp('widgets', name)
}

function exists(name) {
  try {
    getWidget(name)
    return true
  }
  catch(error) {
    return false
  }
}

export {
  getWidget,
  exists
}
