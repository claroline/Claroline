import {getApps} from '#/main/app/plugins'

function getExternalPlugins() {
  const asyncPlugins = getApps('tinymcePlugins')

  return Promise.all(
    Object.keys(asyncPlugins).map(plugin => asyncPlugins[plugin]())
  ).then(() => Object.keys(asyncPlugins))
}

export {
  getExternalPlugins
}