import {LINK_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl'
import {hasPermission} from '#/main/app/security'

import {route} from '#/main/community/user/routing'

export default (users, refresher, path) => ({
  name: 'edit',
  type: LINK_BUTTON,
  icon: 'fa fa-fw fa-pencil',
  label: trans('edit', {}, 'actions'),
  target: route(users[0], path) + '/edit',
  displayed: hasPermission('edit', users[0]),
  primary: true,
  group: trans('management'),
  scope: ['object']
})
