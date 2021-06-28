import {makeActionCreator} from '#/main/app/store/actions'
import {API_REQUEST, url} from '#/main/app/api'
import {constants as actionConstants} from '#/main/app/action/constants'
import {actions as listActions} from '#/main/app/content/list/store/actions'

import {selectors} from '#/plugin/agenda/events/event/store/selectors'

export const EVENT_LOAD = 'EVENT_LOAD'
export const EVENT_SET_LOADED = 'EVENT_SET_LOADED'

export const actions = {}

actions.load = makeActionCreator(EVENT_LOAD, 'event')
actions.setLoaded = makeActionCreator(EVENT_SET_LOADED, 'loaded')

actions.open = (eventId) => (dispatch) => dispatch({
  [API_REQUEST]: {
    url: ['apiv2_event_get', {id: eventId}],
    silent: true,
    request: {
      method: 'GET'
    },
    before: () => dispatch(actions.setLoaded(false)),
    success: (response) => {
      dispatch(actions.load(response))
      dispatch(actions.setLoaded(true))
    }
  }
})

actions.addParticipants = (eventId, users) => (dispatch) => dispatch({
  [API_REQUEST]: {
    url: url(['apiv2_event_add_participants', {id: eventId}], {ids: users.map(user => user.id)}),
    request: {
      method: 'POST'
    },
    success: () => dispatch(listActions.invalidateData(selectors.LIST_NAME))
  }
})

actions.sendInvitations = (eventId, users) => (dispatch) => dispatch({
  [API_REQUEST]: {
    type: actionConstants.ACTION_SEND,
    url: url(['apiv2_event_send_invitations', {id: eventId}], {ids: users.map(user => user.id)}),
    request: {
      method: 'POST'
    },
    success: () => dispatch(listActions.invalidateData(selectors.LIST_NAME))
  }
})
