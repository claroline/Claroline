import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'

import {route} from '#/main/core/tool/routing'
import {constants, declareAction} from '#/main/app/action'
import {hasPermission} from '#/main/app/security'

/**
 * Open tool action.
 */
export default declareAction((tools, toolRefresher, path) => ({
  name: 'open',
  type: LINK_BUTTON,
  icon: 'fa fa-fw fa-arrow-up-right-from-square',
  label: trans('open', {}, 'actions'),
  target: route(tools[0].name, path),
  displayed: hasPermission('open', tools[0]),
  scope: ['object'],
  default: true,
  set: [constants.ACTION_SET_LIST]
}))
