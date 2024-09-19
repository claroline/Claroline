import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {url} from '#/main/app/api'
import {trans, transChoice} from '#/main/app/intl/translation'
import {ASYNC_BUTTON} from '#/main/app/buttons'

import {hasPermission} from '#/main/app/security'

/**
 * Deletes some resource nodes.
 *
 * @param {Array}  resourceNodes  - the list of resource nodes on which we want to execute the action.
 * @param {object} nodesRefresher - an object containing methods to update context in response to action (eg. add, update, delete).
 */
export default (resourceNodes, nodesRefresher) => {
  const processable = resourceNodes.filter(node => !isEmpty(node.parent) && hasPermission('administrate', node))

  return {
    name: 'delete',
    type: ASYNC_BUTTON,
    icon: 'fa fa-fw fa-trash',
    label: trans('delete', {}, 'actions'),
    displayed: 0 !== processable.length,
    dangerous: true,
    confirm: {
      message: transChoice('resources_delete_message', processable.length, {count: '<b class="fw-bold">'+processable.length+'</b>'}, 'resource'),
      additional: trans('irreversible_action_confirm'),
      items:  processable.map(item => ({
        thumbnail: item.thumbnail,
        name: item.name
      }))
    },
    request: {
      url: url(
        ['claro_resource_collection_action', {action: 'delete'}],
        {
          ids: processable.map(resourceNode => resourceNode.id),
          //if selected nodes already are soft deleted
          hard: -1 === processable.findIndex(node => get(node, 'meta.active'))
        }
      ),
      request: {
        method: 'DELETE'
      },
      success: () => nodesRefresher.delete(processable)
    }
  }
}
