import {hasPermission} from '#/main/app/security'
import {trans, transChoice} from '#/main/app/intl/translation'
import {url} from '#/main/app/api'
import {ASYNC_BUTTON} from '#/main/app/buttons'

export default (workspaces, refresher) => ({
  name: 'copy-model',
  type: ASYNC_BUTTON,
  icon: 'fa fa-fw fa-briefcase',
  label: trans('copy-model', {}, 'actions'),
  displayed: !!workspaces.find(workspace => hasPermission('administrate', workspace)),
  confirm: {
    title: transChoice('copy_model_workspaces', workspaces.length, {count: workspaces.length}),
    message: trans('copy_model_workspaces_confirm', {
      workspace_list: workspaces.map(workspace => workspace.name).join(', ')
    })
  },
  request: {
    url: url(['apiv2_workspace_copy_bulk'], {
      ids: workspaces.map(workspace => workspace.id),
      model: 1
    }),
    request: {
      method: 'GET'
    },
    success: (response) => refresher.update(response)
  },
  group: trans('management'),
  scope: ['object', 'collection']
})
