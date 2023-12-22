import {hasPermission} from '#/main/app/security'
import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'

import {route} from '#/plugin/open-badge/badge/routing'

/**
 * Open badge action.
 */
export default (badges, refresher, path) => ({
  name: 'open',
  type: LINK_BUTTON,
  icon: 'fa fa-fw fa-arrow-circle-right',
  label: trans('open', {}, 'actions'),
  displayed: hasPermission('open', badges[0]),
  target: route(badges[0], path),
  scope: ['object'],
  default: true
})
