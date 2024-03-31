import get from 'lodash/get'

import {ASYNC_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {url} from '#/main/app/api'
import {trans} from '#/main/app/intl'
import {hasPermission} from '#/main/app/security'
import {MODAL_USERS} from '#/main/community/modals/users'

export default (badges, refresher) => ({
  name: 'grant',
  type: MODAL_BUTTON,
  icon: 'fa fa-fw fa-user-plus',
  label: trans('grant_users', {}, 'badge'),
  displayed: hasPermission('grant', badges[0]),
  disabled: !get(badges[0], 'meta.enabled'),

  modal: [MODAL_USERS, {
    selectAction: (selected) => ({
      type: ASYNC_BUTTON,
      label: trans('select', {}, 'actions'),
      request: {
        url: url(['apiv2_badge-class_add_users', {badge: badges[0].id}], {ids: selected.map(user => user.id)}),
        request: {
          method: 'PATCH'
        },
        success: () => refresher.update(badges)
      }
    })
  }],
  primary: true,
  scope: ['object'],
  group: trans('management')
})
