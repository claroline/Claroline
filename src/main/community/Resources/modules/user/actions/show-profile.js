import {hasPermission} from '#/main/app/security'
import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'

import {route} from '#/main/community/routing'

export default (users, refresher, path) => ({
  name: 'show-profile',
  type: LINK_BUTTON,
  icon: 'fa fa-fw fa-address-card',
  label: trans('show_profile'),
  target: route(users[0], path),
  displayed: hasPermission('open', users[0]),
  scope: ['object'],
  default: true
})
