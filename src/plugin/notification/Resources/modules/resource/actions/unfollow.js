import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'

export default (resourceNodes, nodesRefresher, path, currentUser) => ({
  name: 'unfollow',
  type: 'async',
  icon: 'fa fa-fw fa-bell-slash',
  label: trans('unfollow', {}, 'actions'),
  displayed: !!currentUser && -1 !== resourceNodes.findIndex(node => !!get(node, 'notifications.enabled')),
  request: {
    url: ['icap_notification_follower_resources_toggle', {
      mode: 'delete',
      ids: resourceNodes.map(node => node.id)
    }],
    request: {
      method: 'PUT'
    },
    success: (response) => nodesRefresher.update(response)
  }
})
