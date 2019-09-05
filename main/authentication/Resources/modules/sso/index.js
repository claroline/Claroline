import {getApp} from '#/main/app/plugins'

function getTool(name) {
  return getApp('sso', name)()
}

export {
  getTool
}
