import {url} from '#/main/app/api'
import {hasPermission} from '#/main/app/security'
import {trans} from '#/main/app/intl/translation'
import {ASYNC_BUTTON} from '#/main/app/buttons'

export default (users, refresher) => ({
  name: 'enable',
  type: ASYNC_BUTTON,
  icon: 'fa fa-fw fa-check-circle',
  label: trans('enable_user'),
  scope: ['object', 'collection'],
  displayed: users.length === users.filter(u => hasPermission('administrate', u)).length &&
    0 < users.filter(u => u.restrictions.disabled).length,
  request: {
    url: url(['apiv2_users_enable'], {ids: users.map(u => u.id)}),
    request: {
      method: 'PUT'
    },
    success: (users) => refresher.update(users)
  },
  group: trans('management'),
  primary: true
})
