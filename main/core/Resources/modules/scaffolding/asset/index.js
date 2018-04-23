import {Asset} from './asset'

// todo : move inside platform module

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
 * Get the path of the a theme file.
 *
 * @return {string}
 */
function theme(assetName) {
  return Asset.path(Asset.theme(assetName))
}

export {
  asset,
  theme
}
