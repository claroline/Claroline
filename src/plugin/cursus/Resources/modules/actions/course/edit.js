import {LINK_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl'
import {hasPermission} from '#/main/app/security'

import {route} from '#/plugin/cursus/routing'

export default (courses, refresher, path) => ({
  name: 'edit',
  type: LINK_BUTTON,
  icon: 'fa fa-fw fa-pencil',
  label: trans('edit', {}, 'actions'),
  target: route(courses[0], null, path) + '/edit',
  displayed: hasPermission('edit', courses[0]),
  primary: true,
  group: trans('management'),
  scope: ['object']
})
