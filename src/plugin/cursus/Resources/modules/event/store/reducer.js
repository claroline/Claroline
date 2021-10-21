import {combineReducers, makeReducer} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store/reducer'

import {LOAD_EVENT, EVENT_SET_LOADED} from '#/plugin/cursus/event/store/actions'
import {selectors} from '#/plugin/cursus/event/store/selectors'

export const reducer = combineReducers({
  loaded: makeReducer(false, {
    [EVENT_SET_LOADED]: (state, action) => action.loaded
  }),
  event: makeReducer(null, {
    [LOAD_EVENT]: (state, action) => action.event
  }),
  registration: makeReducer(null, {
    [LOAD_EVENT]: (state, action) => action.registration
  }),
  // participants
  tutors: makeListReducer(selectors.STORE_NAME+'.tutors', {}, {
    invalidated: makeReducer(false, {
      [LOAD_EVENT]: () => true
    })
  }),
  users: makeListReducer(selectors.STORE_NAME+'.users', {}, {
    invalidated: makeReducer(false, {
      [LOAD_EVENT]: () => true
    })
  }),
  groups: makeListReducer(selectors.STORE_NAME+'.groups', {}, {
    invalidated: makeReducer(false, {
      [LOAD_EVENT]: () => true
    })
  }),
  presences: makeListReducer(selectors.STORE_NAME+'.presences', {
    sortBy: {property: 'user', direction: 1}
  }, {
    invalidated: makeReducer(false, {
      [LOAD_EVENT]: () => true
    })
  })
})
