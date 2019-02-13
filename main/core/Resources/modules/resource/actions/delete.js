import {createElement} from 'react'
import get from 'lodash/get'

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
export default (resourceNodes, nodesRefresher) => ({
  name: 'delete',
  type: ASYNC_BUTTON,
  icon: 'fa fa-fw fa-trash-o',
  label: trans('delete', {}, 'actions'),
  dangerous: true,
  confirm: {
    title: transChoice('resources_delete_confirm', resourceNodes.length),
    subtitle: 1 === resourceNodes.length ? resourceNodes[0].name : transChoice('count_elements', resourceNodes.length, {count: resourceNodes.length}),
    message: transChoice('resources_delete_message', resourceNodes.length, {count: resourceNodes.length})
    //The following block should be commented because it crashes the trash tool for unknwown reason when we close the moel (react freeze error)
    ,
    additional: [
      createElement('div', {
        key: 'additional',
        className: 'modal-body'
      }, resourceNodes.map(node => createElement(ResourceCard, {
        key: node.id,
        className: 'component-container',
        data: node
      })))
    ]
  },
  request: {
    url: url(
      ['claro_resource_collection_action', {action: 'delete'}],
      {
        ids: resourceNodes.map(resourceNode => resourceNode.id),
        //if selected nodes already are soft deleted
        hard: -1 === resourceNodes.findIndex(node => get(node, 'meta.active'))
      }
    ),
    request: {
      method: 'DELETE'
    },
    success: () => nodesRefresher.delete(resourceNodes)
  }
})
