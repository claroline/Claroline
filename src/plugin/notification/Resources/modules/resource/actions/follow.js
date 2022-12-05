import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'

export default (resourceNodes, nodesRefresher, path, currentUser) => ({
  name: 'follow',
  type: 'async',
  icon: 'fa fa-fw fa-bell',
  label: trans('follow', {}, 'actions'),
  displayed: !!currentUser && -1 !== resourceNodes.findIndex(node => !get(node, 'notifications.enabled')),
  request: {
    url: ['icap_notification_follower_resources_toggle', {mode: 'create', ids: resourceNodes.map(node => node.id)}],
    request: {
      method: 'PUT'
    },
    success: (response) => nodesRefresher.update(response)
  }
})
