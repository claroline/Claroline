import {url} from '#/main/app/api'
import {hasPermission} from '#/main/app/security'
import {trans, transChoice} from '#/main/app/intl/translation'
import {ASYNC_BUTTON} from '#/main/app/buttons'

export default (users, refresher) => ({
  name: 'ws-disable',
  type: ASYNC_BUTTON,
  icon: 'fa fa-fw fa-book',
  label: trans('disable_personal_ws'),
  scope: ['object', 'collection'],
  displayed: users.length === users.filter(u => hasPermission('administrate', u)).length &&
    0 < users.filter(u => u.meta.personalWorkspace).length,
  dangerous: true,
  confirm: {
    title: transChoice('disable_personal_workspaces', users.length, {count: users.length}),
    message: trans('disable_personal_workspaces_confirm', {users_list: users.map(u => `${u.firstName} ${u.lastName}`).join(', ')})
  },
  request: {
    url: url(['apiv2_users_pws_delete'], {ids: users.map(u => u.id)}),
    request: {
      method: 'DELETE'
    },
    success: (response) => refresher.update(response)
  },
  group: trans('management')
})
