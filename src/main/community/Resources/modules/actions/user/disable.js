import get from 'lodash/get'

import {url} from '#/main/app/api'
import {hasPermission} from '#/main/app/security'
import {trans, transChoice} from '#/main/app/intl/translation'
import {ASYNC_BUTTON} from '#/main/app/buttons'

export default (users, refresher) => {
  const processable = users.filter(user => hasPermission('administrate', user) && !get(user, 'restrictions.disabled', false))

  return {
    name: 'disable',
    type: ASYNC_BUTTON,
    icon: 'fa fa-fw fa-user-xmark',
    label: trans('disable', {}, 'actions'),
    displayed: 0 !== processable.length,
    dangerous: true,
    confirm: {
      message: transChoice('user_disable_confirm_message', processable.length, {count: '<b class="fw-bold">'+processable.length+'</b>'}, 'community'),
      additional: trans('Les utilisateurs désactivés ne peuvent plus se connecter à la plateforme.'),
      items:  processable.map(item => ({
        thumbnail: item.picture,
        name: item.name
      }))
    },
    request: {
      url: url(['apiv2_user_disable'], {ids: users.map(u => u.id)}),
      request: {
        method: 'PUT'
      },
      success: (response) => refresher.update(response)
    },
    scope: ['object', 'collection'],
    group: trans('management')
  }
}
