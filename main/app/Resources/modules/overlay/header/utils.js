import {getApp} from '#/main/app/plugins'

function getMenu(name) {
  return getApp('header', name)()
}

export {
  getMenu
}
