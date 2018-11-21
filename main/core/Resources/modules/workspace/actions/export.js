import {hasPermission} from '#/main/app/security'
import {trans} from '#/main/app/intl/translation'
import {URL_BUTTON} from '#/main/app/buttons'

// TODO : make it work

export default (workspaces) => ({ // TODO : collection
  name: 'export',
  type: URL_BUTTON,
  icon: 'fa fa-fw fa-download',
  label: trans('export', {}, 'actions'),
  displayed: hasPermission('export', workspaces[0]) && false, // currently broken
  target: ['claro_workspace_export', {workspace: workspaces[0].id}],
  group: trans('transfer'),
  scope: ['object']
})
