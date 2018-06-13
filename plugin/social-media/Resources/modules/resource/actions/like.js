import get from 'lodash/get'

import {trans} from '#/main/core/translation'
import {isAuthenticated} from '#/main/core/user/current'

const action = (resourceNodes) => ({ // todo collection
  name: 'like',
  type: 'async',
  icon: 'fa fa-fw fa-thumbs-o-up',
  label: trans('like', {}, 'actions'),
  displayed: isAuthenticated(),
  subscript: 1 === resourceNodes.length && {
    type: 'label',
    status: 'primary',
    value: get(resourceNodes[0], 'social.likes') || 0
  },
  request: {
    url: ['claro_resource_action', {
      resourceType: resourceNodes[0].meta.type,
      action: 'like',
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
