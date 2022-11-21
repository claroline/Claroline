import {createElement} from 'react'
import get from 'lodash/get'

import {url} from '#/main/app/api/router'
import {trans, transChoice} from '#/main/app/intl/translation'
import {ASYNC_BUTTON} from '#/main/app/buttons'

import {ResourceCard} from '#/main/core/resource/components/card'

/**
 * Restores some soft deleted resource nodes.
 *
 * @param {Array}  resourceNodes  - the list of resource nodes on which we want to execute the action.
 * @param {object} nodesRefresher - an object containing methods to update context in response to action (eg. add, update, delete).
 */
export default (resourceNodes, nodesRefresher) => {
  const processable = resourceNodes.filter(node => !get(node, 'meta.active'))

  return {
    name: 'restore',
    type: ASYNC_BUTTON,
    icon: 'fa fa-fw fa-trash-restore-alt',
    label: trans('restore', {}, 'actions'),
    displayed: 0 !== processable.length,
    confirm: {
      title: transChoice('resources_restore_confirm', processable.length, {}, 'resource'),
      subtitle: 1 === processable.length ? processable[0].name : transChoice('count_elements', processable.length, {count: processable.length}),
      message: transChoice('resources_restore_message', processable.length, {count: processable.length}, 'resource'),
      additional: [
        createElement('div', {
          key: 'additional',
          className: 'modal-body'
        }, processable.map(node => createElement(ResourceCard, {
          key: node.id,
          orientation: 'row',
          size: 'xs',
          data: node
        })))
      ]
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
