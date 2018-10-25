import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {TOOL_SET_CONTEXT} from '#/main/core/tool/store/actions'

const reducer = combineReducers({
  name: makeReducer(null),
  context: makeReducer({}, {
    [TOOL_SET_CONTEXT]: (state, action) => ({
      type: action.contextType,
      data: action.contextData
    })
  })
})

export {
  reducer
}
