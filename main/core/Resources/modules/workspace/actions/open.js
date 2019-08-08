import {hasPermission} from '#/main/app/security'
import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {route} from '#/main/core/workspace/routing'

/**
 * Open workspace action.
 */
export default (workspaces) => ({
  name: 'open',
  type: LINK_BUTTON,
  label: trans('open', {}, 'actions'),
  primary: true,
  displayed: hasPermission('open', workspaces[0]),
  icon: 'fa fa-fw fa-arrow-circle-o-right',
  target: route(workspaces[0]),
  scope: ['object'],
  default: true
})
