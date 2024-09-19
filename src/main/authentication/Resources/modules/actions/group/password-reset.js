import {ASYNC_BUTTON} from '#/main/app/buttons'
import {trans, transChoice} from '#/main/app/intl'
import {url} from '#/main/app/api'
import {hasPermission} from '#/main/app/security'

export default (groups) => {
  const processable = groups.filter(group => hasPermission('administrate', group))

  return {
    name: 'password-reset',
    type: ASYNC_BUTTON,
    icon: 'fa fa-fw fa-user-lock',
    label: trans('reset-password', {}, 'actions'),
    displayed: 0 !== processable.length,
    confirm: {
      message: transChoice('group_password_reset_confirm_message', processable.length, {count: '<b class="fw-bold">'+processable.length+'</b>'}, 'security'),
      additional: trans('password_reset_confirm_help', {}, 'security'),
      button: trans('reset', {}, 'actions'),
      items:  processable.map(item => ({
        thumbnail: item.thumbnail,
        name: item.name
      }))
    },
    request: {
      type: 'send',
      url: url(['apiv2_group_password_reset'], {ids: processable.map(group => group.id)}),
      request: {
        method: 'PUT'
      }
    },
    scope: ['object', 'collection'],
    group: trans('management')
  }
}
