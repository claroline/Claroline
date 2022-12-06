import {hasPermission} from '#/main/app/security'
import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'

import {route} from '#/main/community/role/routing'

/**
 * Open role action.
 */
export default (roles, refresher, path) => ({
  name: 'open',
  type: LINK_BUTTON,
  icon: 'fa fa-fw fa-arrow-circle-right',
  label: trans('open', {}, 'actions'),
  displayed: hasPermission('open', roles[0]),
  target: route(roles[0], path),
  scope: ['object'],
  default: true
})
