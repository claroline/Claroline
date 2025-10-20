import identity from 'lodash/identity'

import {getApp} from '#/main/app/plugins'

/**
 * Gets a data source definition by its name.
 *
 * @param {string} sourceName
 * @param {object} refresher
 * @param {string} contextType
 * @param {object} contextData
 * @param {object} currentUser - the authenticated user
 *
 * @return {Promise.<Object>}
 */
function getSource(sourceName, contextType, contextData, refresher, currentUser) {
  // adds default refresher actions
  const finalRefresher = Object.assign({
    add: identity,
    update: identity,
    delete: identity
  }, refresher)

  // retrieve the data source application
  return getApp('data.sources', sourceName)()
    .then(sourceModule => {
      if (typeof sourceModule.default === 'function') {
        return sourceModule.default(contextType, contextData, finalRefresher, currentUser)
      }

      return sourceModule.default.parameters
    })
}

/**
 * Declare a new data source to the application.
 *
 * NB. DataSource MUST be registered in the `plugin.js` file of its plugin.
 */
function declareDataSource(sourceDefinition) {
  return sourceDefinition
}

export {
  getSource,
  declareDataSource
}
