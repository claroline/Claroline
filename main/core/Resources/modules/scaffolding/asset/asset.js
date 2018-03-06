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
  path(assetName) {
    const element = document.getElementById('baseAsset')

    let basePath = ''
    if (element) {
      basePath = element.innerHTML
    }

    return basePath.trim() + assetName
  },

  /**
   * Get the path of the main theme file.
   *
   * @return {string}
   */
  theme() {
    const element = document.getElementById('homeTheme')

    let basePath = ''
    if (element) {
      basePath = element.innerHTML
    }

    return basePath.trim()
  }

}

export {
  Asset
}
