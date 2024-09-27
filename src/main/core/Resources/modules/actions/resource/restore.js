import get from 'lodash/get'

import {url} from '#/main/app/api/router'
import {trans, transChoice} from '#/main/app/intl/translation'
import {ASYNC_BUTTON} from '#/main/app/buttons'
import {hasPermission} from '#/main/app/security'

/**
 * Restores some soft deleted resource nodes.
 *
 * @param {Array}  resourceNodes  - the list of resource nodes on which we want to execute the action.
 * @param {object} nodesRefresher - an object containing methods to update context in response to action (eg. add, update, delete).
 */
export default (resourceNodes, nodesRefresher) => {
  const processable = resourceNodes.filter(node => !get(node, 'meta.active') && hasPermission('administrate', node))

  return {
    name: 'restore',
    type: ASYNC_BUTTON,
    icon: 'fa fa-fw fa-trash-restore-alt',
    label: trans('restore', {}, 'actions'),
    displayed: 0 !== processable.length,
    dangerous: true,
    confirm: {
      message: transChoice('resources_restore_message', processable.length, {count: '<b class="fw-bold">'+processable.length+'</b>'}, 'resource'),
      items:  processable.map(item => ({
        thumbnail: item.thumbnail,
        name: item.name
      })),
      dangerous: false
    },
    request: {
      url: url(
        ['claro_resource_collection_action', {action: 'restore'}],
        {ids: processable.map(node => node.id)}
      ),
      request: {
        method: 'POST'
      },
      success: (restoredNodes) => nodesRefresher.update(restoredNodes)
    }
  }
}
