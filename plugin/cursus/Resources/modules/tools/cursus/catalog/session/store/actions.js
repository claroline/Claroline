import {API_REQUEST} from '#/main/app/api'
import {makeActionCreator} from '#/main/app/store/actions'

const LOAD_SESSION = 'LOAD_SESSION'
const LOAD_SESSION_USER = 'LOAD_SESSION_USER'
const LOAD_SESSION_QUEUE = 'LOAD_SESSION_QUEUE'
const LOAD_SESSION_FULL = 'LOAD_SESSION_FULL'
const LOAD_EVENTS_REGISTRATION = 'LOAD_EVENTS_REGISTRATION'

const actions = {}

actions.register = (sessionId) => ({
  [API_REQUEST]: {
    url: ['apiv2_cursus_session_self_register', {id: sessionId}],
    request: {
      method: 'PUT'
    },
    success: (data, dispatch) => {
      if (data.registrationDate) {
        dispatch(actions.loadSessionUser(data))
      } else if (data.applicationDate) {
        dispatch(actions.loadSessionQueue(data))
      }
    }
  }
})

actions.fetchSession = (sessionId) => ({
  [API_REQUEST]: {
    url: ['claro_cursus_catalog_session', {session: sessionId}],
    request: {
      method: 'GET'
    },
    success: (data, dispatch) => {
      dispatch(actions.loadSession(data['session']))
      dispatch(actions.loadSessionUser(data['sessionUser']))
      dispatch(actions.loadSessionQueue(data['sessionQueue']))
      dispatch(actions.loadIsFull(data['isFull']))
      dispatch(actions.loadEventsRegistration(data['eventsRegistration']))
    }
  }
})

actions.registerToEvent = (eventId) => ({
  [API_REQUEST]: {
    url: ['apiv2_cursus_session_event_self_register', {id: eventId}],
    request: {
      method: 'PUT'
    },
    success: (data, dispatch) => dispatch(actions.loadEventsRegistration(data))
  }
})

actions.loadSession = makeActionCreator(LOAD_SESSION, 'session')
actions.loadSessionUser = makeActionCreator(LOAD_SESSION_USER, 'sessionUser')
actions.loadSessionQueue = makeActionCreator(LOAD_SESSION_QUEUE, 'sessionQueue')
actions.loadIsFull = makeActionCreator(LOAD_SESSION_FULL, 'isFull')
actions.loadEventsRegistration = makeActionCreator(LOAD_EVENTS_REGISTRATION, 'eventsRegistration')

export {
  actions,
  LOAD_SESSION,
  LOAD_SESSION_USER,
  LOAD_SESSION_QUEUE,
  LOAD_SESSION_FULL,
  LOAD_EVENTS_REGISTRATION
}