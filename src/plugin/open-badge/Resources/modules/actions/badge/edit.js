
import {LINK_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl'
import {hasPermission} from '#/main/app/security'

import {route} from '#/plugin/open-badge/badge/routing'

export default (badges, refresher, path) => ({
  name: 'edit',
  type: LINK_BUTTON,
  icon: 'fa fa-fw fa-pencil',
  label: trans('edit', {}, 'actions'),
  target: route(badges[0], path) + '/edit',
  displayed: hasPermission('edit', badges[0]),
  primary: true,
  group: trans('management'),
  scope: ['object']
})
