import get from 'lodash/get'

import {url} from '#/main/app/api'
import {ASYNC_BUTTON} from '#/main/app/buttons'

import {trans} from '#/main/core/translation'

const action = (resourceNodes, nodesRefresher) => ({
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

export {
  action
}
