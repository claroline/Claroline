import {getApp, getApps} from '#/main/app/plugins'

function getTools() {
  return getApps('tools')
}

function getTool(name) {
  return getApp('tools', name)()
}

export {
  getTools,
  getTool
}
