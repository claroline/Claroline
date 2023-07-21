import {makeReducer, combineReducers} from '#/main/app/store/reducer'

import {
  HEADER_NOTIFICATIONS_LOAD,
  HEADER_NOTIFICATIONS_SET_LOADED
} from '#/plugin/notification/modals/notifications/store/actions'

export const reducer = combineReducers({
  loaded: makeReducer(false, {
    [HEADER_NOTIFICATIONS_LOAD]: () => true,
    [HEADER_NOTIFICATIONS_SET_LOADED]: (state, action) => action.loaded
  }),
  results: makeReducer([], {
    [HEADER_NOTIFICATIONS_LOAD]: (state, action) => action.results
  })
})
