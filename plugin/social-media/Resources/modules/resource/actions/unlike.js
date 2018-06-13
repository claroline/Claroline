import get from 'lodash/get'

import {trans} from '#/main/core/translation'
import {isAuthenticated} from '#/main/core/user/current'

const action = (resourceNodes) => ({ // todo collection
  name: 'unlike',
  type: 'async',
  icon: 'fa fa-fw fa-flip-vertical fa-thumbs-o-up',
  label: trans('unlike', {}, 'actions'),
  displayed: isAuthenticated() && false, // todo find the correct way to display it
  subscript: 1 === resourceNodes.length && {
    type: 'label',
    status: 'primary',
    value: get(resourceNodes[0], 'social.likes') || 0
  },
  request: {
    url: ['claro_resource_action', {
      resourceType: resourceNodes[0].meta.type,
      action: 'unlike',
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
