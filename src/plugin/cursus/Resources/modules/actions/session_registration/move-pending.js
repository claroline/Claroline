import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {declareAction} from '#/main/app/action'
import {ASYNC_BUTTON} from '#/main/app/buttons'
import {trans, transChoice} from '#/main/app/intl'
import {hasPermission} from '#/main/app/security'

export default declareAction((registrations, refresher) => {
  const processable = registrations.filter(registration => !isEmpty(registration.session) && hasPermission('administrate', registration))

  return ({
    name: 'move-pending',
    type: ASYNC_BUTTON,
    icon: 'fa fa-fw fa-hourglass-half',
    label: trans('move-pending', {}, 'actions'),
    displayed: 0 !== processable.length,
    confirm: {
      message: transChoice('session_registration_move_pending_confirm', processable.length, {count: '<b class="fw-bold">'+processable.length+'</b>'}, 'cursus'),
      additional: trans('session_registration_move_pending_additional', {}, 'cursus'),
      items:  processable.map(registration => ({
        thumbnail: registration.user.picture,
        id: registration.user.id,
        name: registration.user.name
      }))
    },
    request: {
      url: ['apiv2_training_session_user_move', {type: get(processable[0], 'type'), targetId: null}],
      request: {
        method: 'PUT',
        body: JSON.stringify({
          sessionUsers: processable.map(registration => registration.id)
        })
      },
      success: () => refresher.delete(processable)
    },
    group: trans('management'),
    scope: ['object', 'collection']
  })
})
