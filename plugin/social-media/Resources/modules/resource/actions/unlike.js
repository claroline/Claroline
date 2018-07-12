import get from 'lodash/get'

import {number} from '#/main/app/intl'
import {trans} from '#/main/core/translation'
import {isAuthenticated} from '#/main/core/user/current'

const action = (resourceNodes) => ({ // todo collection
  name: 'unlike',
  type: 'async',
  icon: 'fa fa-fw fa-flip-vertical fa-thumbs-o-up',
  label: trans('unlike', {}, 'actions'),
  displayed: isAuthenticated() && false, // todo find the correct way to display it
  subscript: 1 === resourceNodes.length ? {
    type: 'label',
    status: 'primary',
    value: number(get(resourceNodes[0], 'social.likes') || 0, true)
  } : undefined,
  request: {
    url: ['icap_socialmedia_unlike', {}],
    request: {
      method: 'POST',
      body: JSON.stringify({resourceId: resourceNodes[0].id})
    }
  }
})

export {
  action
}
