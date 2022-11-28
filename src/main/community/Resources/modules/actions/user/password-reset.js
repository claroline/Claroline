import {hasPermission} from '#/main/app/security'
import {url} from '#/main/app/api'
import {trans} from '#/main/app/intl/translation'
import {ASYNC_BUTTON} from '#/main/app/buttons'

export default (users) => {
  const processable = users.filter(user => hasPermission('administrate', user))

  return {
    name: 'password-reset',
    type: ASYNC_BUTTON,
    icon: 'fa fa-fw fa-user-lock',
    label: trans('password_reset', {}, 'actions'),
    displayed: 0 !== processable.length,
    confirm: true,
    request: {
      url: url(['apiv2_user_password_reset'], {ids: processable.map(user => user.id)}),
      request: {
        method: 'PUT'
      }
    },
    scope: ['object', 'collection'],
    group: trans('management')
  }
}
