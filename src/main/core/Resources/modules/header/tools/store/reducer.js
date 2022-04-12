import {makeReducer, combineReducers} from '#/main/app/store/reducer'

import {
  USER_TOOLS_LOAD,
  USER_TOOLS_SET_LOADED
} from '#/main/core/header/tools/store/actions'

export const reducer = combineReducers({
  loaded: makeReducer(false, {
    [USER_TOOLS_LOAD]: () => true,
    [USER_TOOLS_SET_LOADED]: (state, action) => action.loaded
  }),
  tools: makeReducer([], {
    [USER_TOOLS_LOAD]: (state, action) => action.tools
  })
})
