import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'

import {route} from '#/main/community/organization/routing'

/**
 * Open organization action.
 */
export default (organizations, refresher, path) => ({
  name: 'open',
  type: LINK_BUTTON,
  icon: 'fa fa-fw fa-arrow-circle-right',
  label: trans('open', {}, 'actions'),
  target: route(organizations[0], path),
  scope: ['object'],
  default: true
})
