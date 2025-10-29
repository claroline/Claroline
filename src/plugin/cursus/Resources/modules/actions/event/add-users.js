
import {ASYNC_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {hasPermission} from '#/main/app/security'
import {trans} from '#/main/app/intl/translation'
import {declareAction} from '#/main/app/action'
import {MODAL_USERS} from '#/main/community/modals/users'
import {constants} from '#/plugin/cursus/constants'

export default declareAction((events, refresher) => {
  return {
    name: 'add-users',
    type: MODAL_BUTTON,
    icon: 'fa fa-fw fa-user-plus',
    label: trans('register_users'),
    displayed: hasPermission('follow', events[0]),
    modal: [MODAL_USERS, {
      selectAction: (selected) => ({
        type: ASYNC_BUTTON,
        label: trans('register', {}, 'actions'),
        request: {
          url: ['apiv2_training_event_add_users', {id: events[0].id, type: constants.LEARNER_TYPE}],
          request: {
            method: 'PATCH',
            body: JSON.stringify(selected.map(user => user.id))
          },
          success: () => refresher.update(events[0])
        }
      })
    }],
    scope: ['object']
  }
})
