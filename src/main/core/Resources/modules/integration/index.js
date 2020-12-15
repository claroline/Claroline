import {getApps} from '#/main/app/plugins'

function getIntegrations() {
  const apps = getApps('integration')

  return Promise.all(Object.keys(apps).map(type => apps[type]()))
}

export {
  getIntegrations
}
