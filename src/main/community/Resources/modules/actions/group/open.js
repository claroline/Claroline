import {hasPermission} from '#/main/app/security'
import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'

import {route} from '#/main/community/group/routing'

/**
 * Open group action.
 */
export default (groups, refresher, path) => ({
  name: 'open',
  type: LINK_BUTTON,
  icon: 'fa fa-fw fa-arrow-circle-right',
  label: trans('open', {}, 'actions'),
  displayed: hasPermission('open', groups[0]),
  target: route(groups[0], path),
  scope: ['object'],
  default: true
})
