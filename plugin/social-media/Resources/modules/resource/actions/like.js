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
    url: ['icap_socialmedia_like', {}],
    request: {
      method: 'POST',
      body: JSON.stringify({resourceId: resourceNodes[0].id})
    }
  }
})

export {
  action
}
