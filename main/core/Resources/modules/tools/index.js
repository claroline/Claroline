import {getApp} from '#/main/app/plugins'

function getTool(name) {
  return getApp('tools', name)()
}

export {
  getTool
}
