import {platformConfig} from '#/main/core/platform'
/**
 * Manages assets.
 *
 * (We use an object to be able to mock it in tests)
 */
const Asset = {
  /**
   * Get the path to an asset file in the web directory.
   * (retrieve method should be updated in next version)
   *
   * @param {string} assetName - the name of the asset
   *
   * @returns {string}
   */
  path(assetName = '') {
    const element = document.getElementById('homeAsset')

    let basePath = ''
    if (element) {
      basePath = element.innerHTML
    }

    return basePath.trim() + assetName
  },

  /**
   * Get the path of the main theme file.
   *
   * @todo manage versioning
   *
   * @return {string}
   */
  theme(assetName) {
    return `/themes/${platformConfig('theme.name')}/${assetName}.css`
  }

}

export {
  Asset
}
