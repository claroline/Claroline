import {hasPermission} from '#/main/app/security'
import {trans} from '#/main/app/intl/translation'
import {URL_BUTTON, CALLBACK_BUTTON} from '#/main/app/buttons'

/**
 * Open workspace action.
 */
export default (workspaces) => ({
  name: 'open',
  type: CALLBACK_BUTTON,
  label: trans('open', {}, 'actions'),
  primary: true,
  displayed: hasPermission('open', workspaces[0]),
  icon: 'fa fa-fw fa-arrow-circle-o-right',
  target: ['claro_workspace_open', {
    workspaceId: workspaces[0].id
  }],
  scope: ['object'],
  default: true,
  callback: () => true
})
