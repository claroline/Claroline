import {LINK_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl'
import {hasPermission} from '#/main/app/security'

import {route} from '#/plugin/open-badge/badge/routing'
import {constants, declareAction} from '#/main/app/action'

export default declareAction((badges, refresher, path) => ({
  name: 'edit',
  type: LINK_BUTTON,
  icon: 'fa fa-fw fa-pencil',
  label: trans('edit', {}, 'actions'),
  target: route(badges[0], path) + '/edit',
  displayed: hasPermission('edit', badges[0]),
  primary: true,
  group: trans('management'),
  scope: ['object'],
  set: [constants.ACTION_SET_LIST, constants.ACTION_SET_DETAILS]
}))
