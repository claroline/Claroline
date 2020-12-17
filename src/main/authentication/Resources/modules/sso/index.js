import {getApp} from '#/main/app/plugins'

function getSso(name) {
  return getApp('sso', name)()
}

export {
  getSso
}
