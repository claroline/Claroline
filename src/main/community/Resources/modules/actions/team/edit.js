import {LINK_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl'
import {hasPermission} from '#/main/app/security'

import {route} from '#/main/community/team/routing'

export default (teams, refresher, path) => ({
  name: 'edit',
  type: LINK_BUTTON,
  icon: 'fa fa-fw fa-pencil',
  label: trans('edit', {}, 'actions'),
  target: route(teams[0], path) + '/edit',
  displayed: hasPermission('edit', teams[0]),
  primary: true,
  group: trans('management'),
  scope: ['object']
})
