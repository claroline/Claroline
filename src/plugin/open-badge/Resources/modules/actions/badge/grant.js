import get from 'lodash/get'

import {ASYNC_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl'
import {hasPermission} from '#/main/app/security'
import {MODAL_USERS} from '#/main/community/modals/users'
import {constants, declareAction} from '#/main/app/action'

export default declareAction((badges, refresher) => ({
  name: 'grant',
  type: MODAL_BUTTON,
  icon: 'fa fa-fw fa-user-plus',
  label: trans('grant_users', {}, 'actions'),
  displayed: hasPermission('follow', badges[0]),
  disabled: get(badges[0], 'meta.archived', false),

  modal: [MODAL_USERS, {
    selectAction: (selected) => ({
      type: ASYNC_BUTTON,
      label: trans('select', {}, 'actions'),
      request: {
        url: ['apiv2_badge_add_users', {badge: badges[0].id}],
        request: {
          method: 'PATCH',
          body: JSON.stringify(selected.map(user => user.id))
        },
        success: () => refresher.update(badges)
      }
    })
  }],
  primary: true,
  scope: ['object'],
  group: trans('management'),
  set: [constants.ACTION_SET_LIST, constants.ACTION_SET_DETAILS]
}))
