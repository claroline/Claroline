import {trans} from '#/main/app/intl'

import {createSelector} from 'reselect'
import {API_REQUEST} from '#/main/app/api'

import {MODAL_ALERT} from '#/main/app/modals/alert'
import {makeActionCreator} from '#/main/app/store/actions'
import {combineReducers, makeReducer} from '#/main/app/store/reducer'
import {actions as modalActions} from '#/main/app/overlays/modal/store'

const STORE_NAME = 'presence'
const SIGN_EVENT = 'eventSign'
const CURRENT_EVENT = 'eventCurrent'
const LOAD_EVENT = 'eventLoad'
const CHANGE_EVENT = 'eventChange'

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

const selectors = {
  STORE_NAME,
  currentEvent,
  eventLoaded,
  signature,
  code,
  store
}

const actions = {}

actions.setCode = makeActionCreator(CHANGE_EVENT, 'code')
actions.setSignature = makeActionCreator(SIGN_EVENT, 'signature')
actions.setEventLoaded = makeActionCreator(LOAD_EVENT, 'eventLoaded')
actions.setCurrentEvent = makeActionCreator(CURRENT_EVENT, 'currentEvent')

actions.getEventByCode = (code = null) => ({
  [API_REQUEST]: {
    url: ['apiv2_cursus_event_get', {field: 'code', id: code}],
    success: (response, dispatch) => {
      dispatch(actions.setCurrentEvent(response))
      dispatch(actions.setEventLoaded(true))
    },
    error: (response, status, dispatch) => {
      dispatch(actions.setCurrentEvent(null))
      dispatch(actions.setEventLoaded(true))
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
      dispatch(modalActions.showModal(MODAL_ALERT, {
        type: 'info',
        title: trans('presence_confirm_title', {}, 'presence'),
        message: response.success ?
          trans('presence_confirm_desc', { event_title : event.name }, 'presence') :
          trans('presence_confirm_already', { event_title : event.name }, 'presence')
      }))
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
  })
})


export {
  reducer,
  selectors,
  actions,
  currentEvent,
  eventLoaded,
  signature,
  code
}
