import {url} from '#/main/app/api'
import {hasPermission} from '#/main/app/security'
import {trans} from '#/main/app/intl/translation'
import {ASYNC_BUTTON} from '#/main/app/buttons'

export default (users, refresher) => ({
  name: 'ws-enable',
  type: ASYNC_BUTTON,
  icon: 'fa fa-fw fa-book',
  label: trans('enable_personal_ws'),
  scope: ['object', 'collection'],
  displayed: users.length === users.filter(u => hasPermission('administrate', u)).length &&
    0 < users.filter(u => !u.meta.personalWorkspace).length,
  request: {
    url: url(['apiv2_users_pws_create'], {ids: users.map(u => u.id)}),
    request: {
      method: 'POST'
    },
    success: (users) => refresher.update(users)
  },
  group: trans('management')
})
