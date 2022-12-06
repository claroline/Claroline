import {LINK_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl'
import {hasPermission} from '#/main/app/security'

import {route} from '#/main/community/role/routing'

export default (roles, refresher, path) => ({
  name: 'edit',
  type: LINK_BUTTON,
  icon: 'fa fa-fw fa-pencil',
  label: trans('edit', {}, 'actions'),
  target: route(roles[0], path) + '/edit',
  displayed: hasPermission('edit', roles[0]),
  primary: true,
  group: trans('management'),
  scope: ['object']
})
