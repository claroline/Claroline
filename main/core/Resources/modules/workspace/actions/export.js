import {hasPermission} from '#/main/app/security'
import {trans} from '#/main/app/intl/translation'
import {URL_BUTTON} from '#/main/app/buttons'

// TODO : make it work

export default (workspaces) => ({ // TODO : collection
  name: 'export',
  type: URL_BUTTON,
  icon: 'fa fa-fw fa-download',
  label: trans('export', {}, 'actions'),
  displayed: !!workspaces.find(workspace => hasPermission('administrate', workspace)),
  target: ['apiv2_workspace_export', {id: workspaces[0].id}],
  group: trans('management'),
  scope: ['object']
})
