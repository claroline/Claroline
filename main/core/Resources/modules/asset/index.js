/**
 * Get the path to an asset file in the web directory.
 *
 * @param {string} assetName - the name of the asset
 *
 * @returns {string}
 */
export function asset(assetName) {
  const element = document.getElementById('baseAsset')

  let basePath = ''
  if (element) {
    basePath = element.innerHTML
  }

  return basePath.trim() + assetName
}
