import get from 'lodash/get'

import {trans, transChoice} from '#/main/app/intl/translation'
import {ASYNC_BUTTON} from '#/main/app/buttons'
import {hasPermission} from '#/main/app/security'
import {constants, declareAction} from '#/main/app/action'

/**
 * Restores some soft deleted resource nodes.
 *
 * @param {Array}  resourceNodes  - the list of resource nodes on which we want to execute the action.
 * @param {object} nodesRefresher - an object containing methods to update context in response to action (e.g., add, update, delete).
 */
export default declareAction((resourceNodes, nodesRefresher) => {
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
        thumbnail: item.poster,
        id: item.id,
        name: item.name
      })),
      dangerous: false
    },
    request: {
      url: ['claro_resource_restore'],
      request: {
        method: 'PUT',
        body: JSON.stringify(processable.map(node => node.id))
      },
      success: nodesRefresher.update
    },
    group: trans('management'),
    set: [constants.ACTION_SET_LIST, constants.ACTION_SET_DETAILS, constants.ACTION_SET_ADVANCED],
    managerOnly: true,
    title: trans('restore_resource', {}, 'actions'),
    description: trans('restore_resource_desc', {}, 'actions')
  }
})
