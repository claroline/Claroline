import {param} from '#/main/app/config/parameters'

/**
 * Get the path to an asset file.
 *
 * @param {string} assetName - the name of the asset
 *
 * @returns {string}
 */
function asset(assetName) {
  return `${param('serverUrl')}/${assetName}`
}

export {
  asset
}
