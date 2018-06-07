import get from 'lodash/get'

import {trans} from '#/main/core/translation'
import {isAuthenticated} from '#/main/core/user/current'

const action = (resourceNodes) => ({
  name: 'unfollow',
  type: 'async',
  icon: 'fa fa-fw fa-bell-o-slash',
  label: trans('unfollow', {}, 'actions'),
  displayed: isAuthenticated() && -1 !== resourceNodes.findIndex(node => !!get(node, 'notifications.enabled')),
  request: {
    url: ['claro_resource_action', {  // todo collection
      resourceType: resourceNodes[0].meta.type,
      action: 'unfollow',
      id: resourceNodes[0].id
    }],
    request: {
      method: 'PUT'
    }
  }
})

export {
  action
}
