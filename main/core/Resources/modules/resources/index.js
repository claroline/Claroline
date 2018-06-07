import {getApp} from '#/main/app/plugins'

function getResource(name) {
  return getApp('resources', name)
}

export {
  getResource
}
