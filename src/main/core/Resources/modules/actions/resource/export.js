import {trans} from '#/main/app/intl/translation'
import {DOWNLOAD_BUTTON} from '#/main/app/buttons'
import {url} from '#/main/app/api'
import {hasPermission} from '#/main/app/security'
import {getType} from '#/main/core/resource/utils'
import isEmpty from 'lodash/isEmpty'

/**
 * Exports resource nodes.
 *
 * @param {Array} resourceNodes - the list of resource nodes on which we want to execute the action.
 */
export default (resourceNodes) => {
  const processable = resourceNodes.filter(resourceNode => hasPermission('open', resourceNode) && getType(resourceNode).downloadable)

  return {
    name: 'download',
    type: DOWNLOAD_BUTTON,
    icon: 'fa fa-fw fa-download',
    label: trans('download', {}, 'actions'),
    displayed: !isEmpty(processable),
    scope: ['object', 'collection'],
    file: {
      url: url(
        ['claro_resource_download'],
        {ids: processable.map(resourceNode => resourceNode.id)}
      )
    }
  }
}
