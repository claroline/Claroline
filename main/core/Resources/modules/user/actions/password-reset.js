import {url} from '#/main/app/api'
import {trans} from '#/main/app/intl/translation'
import {ASYNC_BUTTON} from '#/main/app/buttons'

export default (users) => ({
  type: ASYNC_BUTTON,
  icon: 'fa fa-fw fa-user-lock',
  label: trans('reset_password'),
  scope: ['object', 'collection'],
  request: {
    url: url(['apiv2_users_password_reset'], {ids: users.map(u => u.id)}),
    request: {
      method: 'PUT'
    }
  }
})
