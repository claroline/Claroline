import {API_REQUEST} from '#/main/app/api'
import {constants as actionConstants} from '#/main/app/action/constants'
import {actions as listActions} from '#/main/app/content/list/store/actions'

import {selectors} from '#/plugin/agenda/events/event/store/selectors'

export const actions = {}

actions.addParticipants = (eventId, users) => (dispatch) => dispatch({
  [API_REQUEST]: {
    url: ['apiv2_event_add_participants', {id: eventId}],
    request: {
      method: 'POST',
      body: JSON.stringify(users.map(user => user.id))
    },
    success: () => dispatch(listActions.invalidateData(selectors.LIST_NAME))
  }
})

actions.sendInvitations = (eventId, users) => (dispatch) => dispatch({
  [API_REQUEST]: {
    type: actionConstants.ACTION_SEND,
    url: ['apiv2_event_send_invitations', {id: eventId}],
    request: {
      method: 'POST',
      body: JSON.stringify(users.map(user => user.id))
    },
    success: () => dispatch(listActions.invalidateData(selectors.LIST_NAME))
  }
})
