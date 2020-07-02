import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'

import {selectors as cursusSelectors} from '#/plugin/cursus/tools/cursus/store/selectors'
import {
  LOAD_SESSION,
  LOAD_SESSION_USER,
  LOAD_SESSION_QUEUE,
  LOAD_SESSION_FULL,
  LOAD_EVENTS_REGISTRATION
} from '#/plugin/cursus/tools/cursus/catalog/session/store/actions'

const reducer = combineReducers({
  sessions: makeListReducer(cursusSelectors.STORE_NAME + '.catalog.sessions'),
  session: makeReducer(null, {
    [LOAD_SESSION]: (state, action) => action.session
  }),
  sessionUser: makeReducer(null, {
    [LOAD_SESSION_USER]: (state, action) => action.sessionUser
  }),
  sessionQueue: makeReducer(null, {
    [LOAD_SESSION_QUEUE]: (state, action) => action.sessionQueue
  }),
  isFull: makeReducer(false, {
    [LOAD_SESSION_FULL]: (state, action) => action.isFull
  }),
  eventsRegistration: makeReducer({}, {
    [LOAD_EVENTS_REGISTRATION]: (state, action) => action.eventsRegistration
  }),
  events: makeListReducer(cursusSelectors.STORE_NAME + '.catalog.events', {}, {
    invalidated: makeReducer(false, {
      [LOAD_SESSION]: () => true
    })
  })
})

export {
  reducer
}