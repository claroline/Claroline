// External claroline
import {getApps} from '#/main/app/plugins'

// Load external claroline plugins (formula, inwicast etc.)
const loadExternalPlugins = () => {
  const asyncPlugins = getApps('tinymcePlugins')
  let externalPlugins = []
  Object.keys(asyncPlugins).forEach(plugin => {externalPlugins.push(plugin)})
  
  Promise.all(
    Object.keys(asyncPlugins).map(plugin => asyncPlugins[plugin]())
  )
  
  return externalPlugins
}

export {
  loadExternalPlugins
}