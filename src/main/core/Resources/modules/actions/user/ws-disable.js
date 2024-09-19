import {url} from '#/main/app/api'
import {hasPermission} from '#/main/app/security'
import {trans, transChoice} from '#/main/app/intl/translation'
import {ASYNC_BUTTON} from '#/main/app/buttons'

export default (users, refresher) => {
  const processable = users.filter(u => hasPermission('administrate', u) && u.meta.personalWorkspace)

  return {
    name: 'ws-disable',
    type: ASYNC_BUTTON,
    icon: 'fa fa-fw fa-book',
    label: trans('disable_personal_ws'),
    displayed: 0 !== processable.length,
    dangerous: true,
    confirm: {
      message: transChoice('disable_personal_workspaces_confirm', processable.length, {count: '<b class="fw-bold">'+processable.length+'</b>'}),
      additional: trans('irreversible_action_confirm'),
      button: trans('delete', {}, 'actions'),
      items:  processable.map(item => ({
        thumbnail: item.picture,
        name: item.name
      }))
    },
    request: {
      url: url(['apiv2_user_pws_delete'], {ids: users.map(u => u.id)}),
      request: {
        method: 'DELETE'
      },
      success: (response) => refresher.update(response)
    },
    group: trans('management'),
    scope: ['object', 'collection']
  }
}
