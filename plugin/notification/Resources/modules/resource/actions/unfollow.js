import get from 'lodash/get'

import {trans} from '#/main/core/translation'
import {isAuthenticated} from '#/main/core/user/current'

const action = (resourceNodes, nodesRefresher) => ({
  name: 'unfollow',
  type: 'async',
  icon: 'fa fa-fw fa-bell-slash-o',
  label: trans('unfollow', {}, 'actions'),
  displayed: isAuthenticated() && -1 !== resourceNodes.findIndex(node => !!get(node, 'notifications.enabled')),
  request: {
    url: ['icap_notification_follower_resources_toggle', {mode: 'delete', ids: resourceNodes.map(node => node.id)}],
    request: {
      method: 'PUT'
    },
    success: (response) => nodesRefresher.update(response)
  }
})

export {
  action
}
