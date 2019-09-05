import {url} from '#/main/app/api'
import {trans} from '#/main/app/intl/translation'
import {ASYNC_BUTTON} from '#/main/app/buttons'

export default (users, refresher) => ({
  type: ASYNC_BUTTON,
  icon: 'fa fa-fw fa-book',
  label: trans('enable_personal_ws'),
  scope: ['object', 'collection'],
  displayed: 0 < users.filter(u => !u.meta.personalWorkspace).length,
  request: {
    url: url(['apiv2_users_pws_create'], {ids: users.map(u => u.id)}),
    request: {
      method: 'POST'
    },
    success: () => refresher.update()
  }
})
