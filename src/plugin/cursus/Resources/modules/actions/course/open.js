import {hasPermission} from '#/main/app/security'
import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'

import {route} from '#/plugin/cursus/routing'

/**
 * Open course action.
 */
export default (courses, refresher, path) => ({
  name: 'open',
  type: LINK_BUTTON,
  icon: 'fa fa-fw fa-arrow-circle-right',
  label: trans('open', {}, 'actions'),
  displayed: hasPermission('open', courses[0]),
  target: route(courses[0], null, path),
  scope: ['object'],
  default: true
})
