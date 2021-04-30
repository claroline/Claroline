import {param} from '#/main/app/config/parameters'

/**
 * Get the path to an asset file.
 *
 * @param {string} assetName - the name of the asset
 *
 * @returns {string}
 */
function asset(assetName) {
  if (0 === assetName.indexOf('http')) {
    // we already have an absolute url, there is nothing to do
    return assetName
  }

  // for retro compatibility
  // api should always serve absolute urls
  return `${param('serverUrl')}/${assetName}`
}

export {
  asset
}
