import get from 'lodash/get'

import {trans} from '#/main/core/translation'
import {ASYNC_BUTTON} from '#/main/app/buttons'

const action = (resourceNodes, nodesRefresher) => ({ // todo collection
  name: 'restore',
  type: ASYNC_BUTTON,
  icon: 'fa fa-fw fa-recycle',
  label: trans('restore', {}, 'actions'),
  displayed: -1 !== resourceNodes.findIndex(node => !get(node, 'meta.active')),
  dangerous: true,
  confirm: {
    title: trans('resources_delete_confirm'),
    message: trans('resources_delete_message')
  },
  request: {
    url: ['claro_resource_action', {
      type: resourceNodes[0].meta.type,
      action: 'restore',
      id: resourceNodes[0].id
    }],
    request: {
      method: 'POST'
    },
    success: () => nodesRefresher.update(resourceNodes)
  }
})

export {
  action
}
