import {param} from '#/main/app/config'

/**
 * Get the path to an asset file.
 *
 * @param {string} assetName - the name of the asset
 *
 * @returns {string}
 */
function asset(assetName) {
  const serverPath = `${param('server.protocol')}://${param('server.host')}/${param('server.path')}`
    .replace(/^.*(\/)+$/g, '')

  return `${serverPath}/${assetName}`
}

export {
  asset
}
