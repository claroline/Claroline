import {Asset} from './asset'

/**
 * Get the path to an asset file.
 *
 * @param {string} assetName - the name of the asset
 *
 * @returns {string}
 */
function asset(assetName) {
  return Asset.path(assetName)
}

/**
 * Get the path of the main theme file.
 *
 * @return {string}
 */
function theme() {
  return Asset.theme()
}

export {
  asset,
  theme
}
