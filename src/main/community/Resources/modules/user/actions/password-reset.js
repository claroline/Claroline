import {hasPermission} from '#/main/app/security'
import {url} from '#/main/app/api'
import {trans} from '#/main/app/intl/translation'
import {ASYNC_BUTTON} from '#/main/app/buttons'

export default (users) => ({
  name: 'password-reset',
  type: ASYNC_BUTTON,
  icon: 'fa fa-fw fa-user-lock',
  label: trans('reset_password'),
  scope: ['object', 'collection'],
  displayed: -1 !== users.findIndex(user => hasPermission('administrate', user)),
  request: {
    url: url(['apiv2_users_password_reset'], {ids: users.map(u => u.id)}),
    request: {
      method: 'PUT'
    }
  },
  group: trans('management')
})
