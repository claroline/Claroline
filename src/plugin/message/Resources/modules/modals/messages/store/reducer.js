import {makeReducer, combineReducers} from '#/main/app/store/reducer'

import {
  HEADER_MESSAGES_LOAD,
  HEADER_MESSAGES_SET_LOADED
} from '#/plugin/message/modals/messages/store/actions'

export const reducer = combineReducers({
  loaded: makeReducer(false, {
    [HEADER_MESSAGES_LOAD]: () => true,
    [HEADER_MESSAGES_SET_LOADED]: (state, action) => action.loaded
  }),
  results: makeReducer([], {
    [HEADER_MESSAGES_LOAD]: (state, action) => action.results
  })
})
