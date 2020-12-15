import {makeReducer, combineReducers} from '#/main/app/store/reducer'

import {
  HISTORY_LOAD,
  HISTORY_SET_LOADED
} from '#/plugin/history/header/history/store/actions'

export const reducer = combineReducers({
  loaded: makeReducer(false, {
    [HISTORY_LOAD]: () => true,
    [HISTORY_SET_LOADED]: (state, action) => action.loaded
  }),
  results: makeReducer({}, {
    [HISTORY_LOAD]: (state, action) => action.history
  })
})
