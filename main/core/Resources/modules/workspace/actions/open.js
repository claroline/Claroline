import {hasPermission} from '#/main/app/security'
import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'

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
  target: `/desktop/workspaces/open/${workspaces[0].id}`,
  scope: ['object'],
  default: true
})
