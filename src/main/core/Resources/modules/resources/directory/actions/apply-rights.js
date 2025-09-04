import {trans, transChoice} from '#/main/app/intl/translation'
import {ASYNC_BUTTON} from '#/main/app/buttons'

import {hasPermission} from '#/main/app/security'
import {constants, declareAction} from '#/main/app/action'

/**
 * Recursively applies directory rights to all of its children.
 *
 * @param {Array}  resourceNodes  - the list of resource nodes on which we want to execute the action.
 * @param {object} nodesRefresher - an object containing methods to update context in response to action (e.g., add, update, delete).
 */
export default declareAction((resourceNodes, nodesRefresher) => {
  const processable = resourceNodes.filter(node => hasPermission('administrate', node))

  return {
    name: 'apply-rights',
    type: ASYNC_BUTTON,
    icon: 'fa fa-fw fa-key',
    label: trans('apply_rights', {}, 'actions'),
    displayed: 0 !== processable.length,
    confirm: {
      message: transChoice('apply_rights_confirm', processable.length, {count: '<b class="fw-bold">'+processable.length+'</b>'}, 'actions'),
      items:  processable.map(item => ({
        thumbnail: item.poster,
        id: item.id,
        name: item.name
      }))
    },
    request: {
      url: ['apiv2_resource_directory_apply_rights', {id: resourceNodes[0].id}],
      request: {
        method: 'PUT'
      },
      success: nodesRefresher.update
    },
    group: trans('management'),
    set: [constants.ACTION_SET_ADVANCED],
    scope: [constants.ACTION_SCOPE_OBJECT],
    title: trans('apply_rights_title', {}, 'actions'),
    description: trans('apply_rights_desc', {}, 'actions'),
    managerOnly: true
  }
})
