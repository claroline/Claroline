import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {SECURITY_USER_CHANGE} from '#/main/app/security/store/actions'
import {CONTEXT_OPEN} from '#/main/app/context/store/actions'
import {
  TOOL_OPEN,
  TOOL_SET_LOADED
} from '#/main/core/tool/store/actions'

const reducer = combineReducers({
  name: makeReducer(null, {
    [CONTEXT_OPEN]: () => null,
    [TOOL_OPEN]: (state, action) => action.name
  }),

  loaded: makeReducer(false, {
    [SECURITY_USER_CHANGE]: () => false,
    [CONTEXT_OPEN]: () => false,
    [TOOL_SET_LOADED]: (state, action) => action.loaded,
    [TOOL_OPEN]: () => false
  })
})

export {
  reducer
}
