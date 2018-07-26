import {param} from '#/main/app/config'
import trim from 'lodash/trim'

/**
 * Get the path to an asset file.
 *
 * @param {string} assetName - the name of the asset
 *
 * @returns {string}
 */
function asset(assetName) {
  const serverPath = `${param('server.protocol')}://${param('server.host')}/${trim(param('server.path'), '/')}`

  return `${serverPath}/${assetName}`
}

export {
  asset
}
