import {ASYNC_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl'
import {url} from '#/main/app/api'
import {hasPermission} from '#/main/app/security'

export default (groups) => {
  const processable = groups.filter(group => hasPermission('administrate', group))

  return {
    name: 'password-reset',
    type: ASYNC_BUTTON,
    icon: 'fa fa-fw fa-user-lock',
    label: trans('password_reset', {}, 'actions'),
    displayed: 0 !== processable.length,
    confirm: {
      title: trans('group_password_reset'),
      message: trans('send_password_reset', {
        groups: processable.map(group => group.name).join(', ')
      })
    },
    request: {
      url: url(['apiv2_group_password_reset'], {ids: processable.map(group => group.id)}),
      request: {
        method: 'PUT'
      }
    },
    scope: ['object', 'collection'],
    group: trans('management')
  }
}
