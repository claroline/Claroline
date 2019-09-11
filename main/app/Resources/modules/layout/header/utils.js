import {getApp, getApps} from '#/main/app/plugins'

function getMenus() {
  const apps = getApps('header')

  return Promise.all(Object.keys(apps).map(type => apps[type]()))
}

function getMenu(name) {
  return getApp('header', name)()
}

export {
  getMenu,
  getMenus
}
