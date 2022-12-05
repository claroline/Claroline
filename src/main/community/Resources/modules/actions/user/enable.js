import get from 'lodash/get'

import {url} from '#/main/app/api'
import {hasPermission} from '#/main/app/security'
import {trans} from '#/main/app/intl/translation'
import {ASYNC_BUTTON} from '#/main/app/buttons'

export default (users, refresher) => {
  const processable = users.filter(user => hasPermission('administrate', user) && get(user, 'restrictions.disabled', false))

  return {
    name: 'enable',
    type: ASYNC_BUTTON,
    icon: 'fa fa-fw fa-user-check',
    label: trans('enable', {}, 'actions'),
    scope: ['object', 'collection'],
    displayed: 0 !== processable.length,
    request: {
      url: url(['apiv2_users_enable'], {ids: users.map(u => u.id)}),
      request: {
        method: 'PUT'
      },
      success: (response) => refresher.update(response)
    },
    group: trans('management'),
    primary: true
  }
}
