import get from 'lodash/get'

import {trans} from '#/main/core/translation'
import {isAuthenticated} from '#/main/core/user/current'

const action = (resourceNodes) => ({
  name: 'follow',
  type: 'async',
  icon: 'fa fa-fw fa-bell-o',
  label: trans('follow', {}, 'actions'),
  displayed: isAuthenticated() && -1 !== resourceNodes.findIndex(node => !get(node, 'notifications.enabled')),
  request: {
    url: ['icap_notification_follower_resources_toggle', {ids: resourceNodes.map(node => node.id)}],
    request: {
      method: 'PUT'
    }
  }
})

export {
  action
}
