import {createElement} from 'react'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {url} from '#/main/app/api'
import {trans, transChoice} from '#/main/app/intl/translation'
import {ASYNC_BUTTON} from '#/main/app/buttons'

import {ResourceCard} from '#/main/core/resource/components/card'

/**
 * Deletes some resource nodes.
 *
 * @param {Array}  resourceNodes  - the list of resource nodes on which we want to execute the action.
 * @param {object} nodesRefresher - an object containing methods to update context in response to action (eg. add, update, delete).
 */
export default (resourceNodes, nodesRefresher) => {
  const processable = resourceNodes.filter(node => !isEmpty(node.parent))

  return {
    name: 'delete',
    type: ASYNC_BUTTON,
    icon: 'fa fa-fw fa-trash-o',
    label: trans('delete', {}, 'actions'),
    displayed: 0 !== processable.length,
    dangerous: true,
    confirm: {
      title: transChoice('resources_delete_confirm', processable.length, {}, 'resource'),
      subtitle: 1 === processable.length ? processable[0].name : transChoice('count_elements', processable.length, {count: processable.length}),
      message: transChoice('resources_delete_message', processable.length, {count: processable.length}, 'resource'),
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
