import {getApp} from '#/main/app/plugins'

// TODO : filter sources from disabled plugins

/**
 * Gets a data source definition by its name.
 *
 * @param {string} sourceName
 *
 * @return {Promise.<Object>}
 */
function getSource(sourceName) {
  // retrieve the data source application
  return getApp('data.sources', sourceName)()
}

export {
  getSource
}
