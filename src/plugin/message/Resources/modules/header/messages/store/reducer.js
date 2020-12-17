import {makeReducer, combineReducers} from '#/main/app/store/reducer'

import {
  HEADER_MESSAGES_LOAD,
  HEADER_MESSAGES_SET_LOADED,
  HEADER_MESSAGES_COUNT
} from '#/plugin/message/header/messages/store/actions'

export const reducer = combineReducers({
  count: makeReducer(0, {
    [HEADER_MESSAGES_COUNT]: (state, action) => action.count
  }),
  loaded: makeReducer(false, {
    [HEADER_MESSAGES_LOAD]: () => true,
    [HEADER_MESSAGES_SET_LOADED]: (state, action) => action.loaded
  }),
  results: makeReducer([], {
    [HEADER_MESSAGES_LOAD]: (state, action) => action.results
  })
})
