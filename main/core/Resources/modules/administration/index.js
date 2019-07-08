import {getApp} from '#/main/app/plugins'

function getTool(name) {
  return getApp('administration', name)()
}

export {
  getTool
}
