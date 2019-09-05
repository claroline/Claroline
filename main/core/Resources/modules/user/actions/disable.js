import {url} from '#/main/app/api'
import {trans, transChoice} from '#/main/app/intl/translation'
import {ASYNC_BUTTON} from '#/main/app/buttons'

export default (users, refresher) => ({
  type: ASYNC_BUTTON,
  icon: 'fa fa-fw fa-times-circle',
  label: trans('disable_user'),
  scope: ['object', 'collection'],
  displayed: 0 < users.filter(u => !u.restrictions.disabled).length,
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
    success: () => refresher.update()
  }
})
