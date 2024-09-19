import {hasPermission} from '#/main/app/security'
import {url} from '#/main/app/api'
import {trans, transChoice} from '#/main/app/intl/translation'
import {ASYNC_BUTTON} from '#/main/app/buttons'

export default (users) => {
  const processable = users.filter(user => hasPermission('administrate', user))

  return {
    name: 'password-reset',
    type: ASYNC_BUTTON,
    icon: 'fa fa-fw fa-user-lock',
    label: trans('reset-password', {}, 'actions'),
    displayed: 0 !== processable.length,
    confirm: {
      message: transChoice('user_password_reset_confirm_message', processable.length, {count: '<b class="fw-bold">'+processable.length+'</b>'}, 'security'),
      additional: trans('password_reset_confirm_help', {}, 'security'),
      button: trans('reset', {}, 'actions'),
      items:  processable.map(item => ({
        thumbnail: item.picture,
        name: item.name
      }))
    },
    request: {
      type: 'send',
      url: url(['apiv2_user_password_reset'], {ids: processable.map(user => user.id)}),
      request: {
        method: 'PUT'
      }
    },
    scope: ['object', 'collection'],
    group: trans('management')
  }
}
