import {trans} from '#/main/app/intl/translation'
import {DOWNLOAD_BUTTON} from '#/main/app/buttons'
import {url} from '#/main/app/api'

/**
 * Exports resource nodes.
 *
 * @param {Array} resourceNodes - the list of resource nodes on which we want to execute the action.
 */
export default (resourceNodes) => ({ // todo collection
  name: 'export',
  type: DOWNLOAD_BUTTON,
  icon: 'fa fa-fw fa-download',
  label: trans('export', {}, 'actions'),
  file: {
    url: url(
      ['claro_resource_download'],
      {ids: resourceNodes.map(resourceNode => resourceNode.id)}
    )
  }
})
