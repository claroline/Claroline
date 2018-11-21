import get from 'lodash/get'

import {url} from '#/main/app/api'
import {ASYNC_BUTTON} from '#/main/app/buttons'

import {trans} from '#/main/app/intl/translation'

/**
 * Deletes some resource nodes.
 *
 * @param {Array}  resourceNodes  - the list of resource nodes on which we want to execute the action.
 * @param {object} nodesRefresher - an object containing methods to update context in response to action (eg. add, update, delete).
 */
export default (resourceNodes, nodesRefresher) => ({
  name: 'delete',
  type: ASYNC_BUTTON,
  icon: 'fa fa-fw fa-trash-o',
  label: trans('delete', {}, 'actions'),
  displayed: -1 !== resourceNodes.findIndex(node => get(node, 'meta.active')),
  dangerous: true,
  confirm: {
    title: trans('resources_delete_confirm'),
    message: trans('resources_delete_message')
  },
  request: {
    url: url(
      ['claro_resource_collection_action', {action: 'delete'}],
      {ids: resourceNodes.map(resourceNode => resourceNode.id)}
    ),
    request: {
      method: 'DELETE'
    },
    success: () => nodesRefresher.delete(resourceNodes)
  }
})
