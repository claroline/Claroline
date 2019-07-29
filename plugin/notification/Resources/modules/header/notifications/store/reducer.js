import {makeReducer, combineReducers} from '#/main/app/store/reducer'

import {
  NOTIFICATIONS_LOAD,
  NOTIFICATIONS_SET_LOADED,
  NOTIFICATIONS_COUNT
} from '#/plugin/notification/header/notifications/store/actions'

export const reducer = combineReducers({
  count: makeReducer(0, {
    [NOTIFICATIONS_COUNT]: (state, action) => action.count
  }),
  loaded: makeReducer(false, {
    [NOTIFICATIONS_LOAD]: () => true,
    [NOTIFICATIONS_SET_LOADED]: (state, action) => action.loaded
  }),
  results: makeReducer([], {
    [NOTIFICATIONS_LOAD]: (state, action) => action.results
  })
})
