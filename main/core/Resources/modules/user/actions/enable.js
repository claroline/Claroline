import {url} from '#/main/app/api'
import {trans} from '#/main/app/intl/translation'
import {ASYNC_BUTTON} from '#/main/app/buttons'

export default (users, refresher) => ({
  type: ASYNC_BUTTON,
  icon: 'fa fa-fw fa-check-circle',
  label: trans('enable_user'),
  scope: ['object', 'collection'],
  displayed: 0 < users.filter(u => u.restrictions.disabled).length,
  request: {
    url: url(['apiv2_users_enable'], {ids: users.map(u => u.id)}),
    request: {
      method: 'PUT'
    },
    success: () => refresher.update()
  }
})
