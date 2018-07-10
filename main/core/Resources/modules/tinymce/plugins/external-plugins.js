// External claroline
import {getApps} from '#/main/app/plugins'

// Load external claroline plugins (formula, inwicast etc.)
const loadExternalPlugins = (callback) => {
  const asyncPlugins = getApps('tinymcePlugins')
  Promise.all(
    Object.keys(asyncPlugins).map(plugin => asyncPlugins[plugin]())
  ).then(callback, callback)
}

const getExternalPlugins = () => {
  const asyncPlugins = getApps('tinymcePlugins')
  let externalPlugins = []
  Object.keys(asyncPlugins).forEach(plugin => externalPlugins.push(plugin))
  
  return externalPlugins
}

export {
  loadExternalPlugins,
  getExternalPlugins
}