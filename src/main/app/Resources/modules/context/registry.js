import {getApps} from '#/main/app/plugins'
import isEmpty from 'lodash/isEmpty'

// memoize loaded context as a small performance optimization
// apps defined in plugins can not change at runtime.
let loadedContexts = []

/**
 * Get contexts definitions in the plugin registry.
 */
function getContexts() {
  if (!isEmpty(loadedContexts)) {
    return Promise.resolve(loadedContexts)
  }
  // get all contexts declared in the app
  const contexts = getApps('context')

  return Promise.all(
    // boot contexts applications
    Object.keys(contexts).map(contextName => contexts[contextName]())
  ).then(loadedContexts => loadedContexts
    .map(contextModule => contextModule.default)
  ).then(contexts => loadedContexts = contexts)
}

export {
  getContexts
}
