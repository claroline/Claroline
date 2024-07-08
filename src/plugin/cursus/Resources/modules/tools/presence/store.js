import {createSelector} from 'reselect'
import {API_REQUEST} from '#/main/app/api'
import {constants} from '#/plugin/cursus/constants'
import {makeActionCreator} from '#/main/app/store/actions'
import {combineReducers, makeReducer} from '#/main/app/store/reducer'

const STORE_NAME = 'presence'
const SIGN_EVENT = 'eventSign'
const CURRENT_EVENT = 'eventCurrent'
const LOAD_EVENT = 'eventLoad'
const CHANGE_EVENT = 'eventChange'
const EVENT_SIGNED = 'eventSigned'

const store = (state) => state[STORE_NAME]

const currentEvent = createSelector(
  [store],
  (store) => store.currentEvent
)

const eventLoaded = createSelector(
  [store],
  (store) => store.eventLoaded
)

const signature = createSelector(
  [store],
  (store) => store.signature
)

const code = createSelector(
  [store],
  (store) => store.code
)

const eventSigned = createSelector(
  [store],
  (store) => store.eventSigned
)

const selectors = {
  STORE_NAME,
  currentEvent,
  eventLoaded,
  signature,
  code,
  eventSigned,
  store
}

const actions = {}

actions.setCode = makeActionCreator(CHANGE_EVENT, 'code')
actions.setSignature = makeActionCreator(SIGN_EVENT, 'signature')
actions.setEventLoaded = makeActionCreator(LOAD_EVENT, 'eventLoaded')
actions.setCurrentEvent = makeActionCreator(CURRENT_EVENT, 'currentEvent')
actions.setEventSigned = makeActionCreator(EVENT_SIGNED, 'eventSigned')

actions.getEventByCode = (code = null) => ({
  [API_REQUEST]: {
    url: ['apiv2_cursus_event_presence_check', {code: code}],
    success: (response, dispatch) => {
      if (typeof response.status !== 'undefined' && constants.PRESENCE_STATUS_PRESENT === response.status) {
        dispatch(actions.setEventSigned(true))
      }
      dispatch(actions.setCurrentEvent(response.event))
      dispatch(actions.setEventLoaded(true))
    },
    error: (response, status, dispatch) => {
      if (status === 404) {
        dispatch(actions.setCurrentEvent(null))
        dispatch(actions.setEventLoaded(true))
      }
    }
  }
})

actions.signPresence = (event, signature) => ({
  [API_REQUEST]: {
    url: ['apiv2_cursus_event_presence_sign'],
    silent: true,
    request: {
      method: 'PUT',
      body: JSON.stringify({
        event: event,
        signature: signature
      })
    },
    success: (response, dispatch) => {
      dispatch(actions.setEventSigned(true))
    }
  }
})

const reducer = combineReducers({
  currentEvent : makeReducer(null, {
    [CURRENT_EVENT]: (state, action) => action.currentEvent
  }),
  eventLoaded : makeReducer(false, {
    [LOAD_EVENT]: (state, action) => action.eventLoaded
  }),
  code : makeReducer('', {
    [CHANGE_EVENT]: (state, action) => action.code
  }),
  signature : makeReducer('', {
    [SIGN_EVENT]: (state, action) => action.signature
  }),
  eventSigned : makeReducer(null, {
    [EVENT_SIGNED]: (state, action) => action.eventSigned
  })
})


export {
  reducer,
  selectors,
  actions,
  currentEvent,
  eventLoaded,
  signature,
  eventSigned,
  code
}
