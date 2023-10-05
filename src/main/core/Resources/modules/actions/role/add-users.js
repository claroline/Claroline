import {trans} from '#/main/app/intl/translation'
import {url} from '#/main/app/api'

import {ASYNC_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {MODAL_USERS} from '#/main/community/modals/users'

export default (role, refresher) => ({
  name: 'add-users',
  type: MODAL_BUTTON,
  icon: 'fa fa-fw fa-user-plus',
  label: trans('add_user'),
  modal: [MODAL_USERS, {
    selectAction: (users) => ({
      type: ASYNC_BUTTON,
      request: {
        url: url(['apiv2_role_add_users', {id: role[0].id}], {ids: users}),
        request: {
          method: 'PATCH'
        },
        success: () => refresher.update(role)
      }
    })
  }],
  group: trans('registration'),
  scope: ['object', 'collection']
})
