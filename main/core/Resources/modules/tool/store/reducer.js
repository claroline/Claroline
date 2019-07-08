import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {
  TOOL_OPEN,
  TOOL_CLOSE,
  TOOL_SET_LOADED
} from '#/main/core/tool/store/actions'

const reducer = combineReducers({
  loaded: makeReducer(false, {
    [TOOL_SET_LOADED]: () => true,
    [TOOL_CLOSE]: () => false
  }),
  name: makeReducer(null, {
    [TOOL_OPEN]: (state, action) => action.name,
    [TOOL_CLOSE]: () => null
  }),
  basePath: makeReducer('', {
    [TOOL_OPEN]: (state, action) => action.basePath
  }),
  currentContext: makeReducer({}, {
    [TOOL_OPEN]: (state, action) => action.context,
    [TOOL_CLOSE]: () => ({})
  })
})

export {
  reducer
}
