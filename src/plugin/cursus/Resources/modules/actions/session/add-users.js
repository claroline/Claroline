import get from 'lodash/get'

import {ASYNC_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {hasPermission} from '#/main/app/security'
import {trans} from '#/main/app/intl/translation'
import {declareAction} from '#/main/app/action'
import {MODAL_USERS} from '#/main/community/modals/users'
import {constants} from '#/plugin/cursus/constants'

export default declareAction((sessions, refresher) => {
  return {
    name: 'add-users',
    type: MODAL_BUTTON,
    icon: 'fa fa-fw fa-user-plus',
    label: trans('register_users'),
    displayed: hasPermission('follow', sessions[0]) && !get(sessions[0], 'meta.canceled', false),
    modal: [MODAL_USERS, {
      selectAction: (selected) => ({
        type: ASYNC_BUTTON,
        label: trans('register', {}, 'actions'),
        request: {
          url: ['apiv2_cursus_session_add_users', {id: sessions[0].id, type: constants.LEARNER_TYPE}],
          request: {
            method: 'PATCH',
            body: JSON.stringify(selected.map(user => user.id))
          },
          success: () => refresher.update(sessions[0])
        }
      })
    }],
    scope: ['object']
  }
})
