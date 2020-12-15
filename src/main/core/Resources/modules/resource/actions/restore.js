import get from 'lodash/get'

import {url} from '#/main/app/api/router'
import {trans, transChoice} from '#/main/app/intl/translation'
import {ASYNC_BUTTON} from '#/main/app/buttons'

/**
 * Restores some soft deleted resource nodes.
 *
 * @param {Array}  resourceNodes  - the list of resource nodes on which we want to execute the action.
 * @param {object} nodesRefresher - an object containing methods to update context in response to action (eg. add, update, delete).
 */
export default (resourceNodes, nodesRefresher) => ({
  name: 'restore',
  type: ASYNC_BUTTON,
  icon: 'fa fa-fw fa-trash-restore-alt',
  label: trans('restore', {}, 'actions'),
  displayed: -1 !== resourceNodes.findIndex(node => !get(node, 'meta.active')),
  confirm: {
    title: transChoice('resources_restore_confirm', resourceNodes.length),
    subtitle: 1 === resourceNodes.length ? resourceNodes[0].name : transChoice('count_elements', resourceNodes.length, {count: resourceNodes.length}),
    message: transChoice('resources_restore_message', resourceNodes.length)
  },
  request: {
    url: url(
      ['claro_resource_collection_action', {action: 'restore'}],
      {ids: resourceNodes.map(node => node.id)}
    ),
    request: {
      method: 'POST'
    },
    success: (restoredNodes) => nodesRefresher.update(restoredNodes)
  }
})
