import {makeActionCreator} from '#/main/app/store/actions'
import {API_REQUEST} from '#/main/app/api'

export const EVENT_LOAD = 'EVENT_LOAD'
export const EVENT_SET_LOADED = 'EVENT_SET_LOADED'

export const actions = {}

actions.load = makeActionCreator(EVENT_LOAD, 'event')
actions.setLoaded = makeActionCreator(EVENT_SET_LOADED, 'loaded')

actions.open = (eventId) => (dispatch) => dispatch({
  [API_REQUEST]: {
    url: ['apiv2_event_get', {id: eventId}],
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

actions.sendInvitations = (eventId) => (dispatch) => dispatch({
  [API_REQUEST]: {
    url: ['apiv2_task_mark_done', {ids: [eventId]}],
    request: {
      method: 'PUT'
    },
    success: (response) => dispatch(actions.load(response[0]))
  }
})
