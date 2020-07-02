import {API_REQUEST} from '#/main/app/api'
import {makeActionCreator} from '#/main/app/store/actions'

const LOAD_SESSION_USER = 'LOAD_SESSION_USER'
const LOAD_SESSION_QUEUE = 'LOAD_SESSION_QUEUE'

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

actions.loadSessionUser = makeActionCreator(LOAD_SESSION_USER, 'sessionUser')
actions.loadSessionQueue = makeActionCreator(LOAD_SESSION_QUEUE, 'sessionQueue')

export {
  actions,
  LOAD_SESSION_USER,
  LOAD_SESSION_QUEUE
}