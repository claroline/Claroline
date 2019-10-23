import {url} from '#/main/app/api'
import {hasPermission} from '#/main/app/security'
import {trans, transChoice} from '#/main/app/intl/translation'
import {ASYNC_BUTTON} from '#/main/app/buttons'

export default (users, refresher) => ({
  name: 'disable',
  type: ASYNC_BUTTON,
  icon: 'fa fa-fw fa-times-circle',
  label: trans('disable_user'),
  scope: ['object', 'collection'],
  displayed: users.length === users.filter(u => hasPermission('administrate', u)).length &&
    0 < users.filter(u => !u.restrictions.disabled).length,
  dangerous: true,
  confirm: {
    title: transChoice('disable_users', users.length, {count: users.length}),
    message: trans('disable_users_confirm', {users_list: users.map(u => `${u.firstName} ${u.lastName}`).join(', ')})
  },
  request: {
    url: url(['apiv2_users_disable'], {ids: users.map(u => u.id)}),
    request: {
      method: 'PUT'
    },
    success: (users) => refresher.update(users)
  },
  group: trans('management')
})
