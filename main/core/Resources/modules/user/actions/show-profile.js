import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'

import {route} from '#/main/core/user/routing'

export default (users, refresher, path) => ({
  type: LINK_BUTTON,
  icon: 'fa fa-fw fa-address-card',
  label: trans('show_profile'),
  target: route(users[0], path),
  scope: ['object'],
  default: true
})
