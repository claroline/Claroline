import get from 'lodash/get'
import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {SECURITY_USER_CHANGE} from '#/main/app/security/store/actions'
import {
  TOOL_OPEN,
  TOOL_CLOSE,
  TOOL_LOAD,
  TOOL_SET_LOADED,
  TOOL_SET_ACCESS_DENIED,
  TOOL_SET_NOT_FOUND,
  TOOL_TOGGLE_FULLSCREEN,
  TOOL_SET_FULLSCREEN
} from '#/main/core/tool/store/actions'

const reducer = combineReducers({
  loaded: makeReducer(false, {
    [SECURITY_USER_CHANGE]: () => false,
    [TOOL_SET_LOADED]: (state, action) => action.loaded,
    [TOOL_CLOSE]: () => false
  }),
  accessDenied: makeReducer(false, {
    [TOOL_SET_ACCESS_DENIED]: (state, action) => action.accessDenied,
    [TOOL_OPEN]: () => false
  }),
  notFound: makeReducer(false, {
    [TOOL_SET_NOT_FOUND]: (state, action) => action.notFound,
    [TOOL_OPEN]: () => false
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
  }),
  fullscreen: makeReducer(false, {
    [TOOL_LOAD]: (state, action) => get(action.toolData, 'data.display.fullscreen') || false,
    [TOOL_TOGGLE_FULLSCREEN]: (state) => !state,
    [TOOL_SET_FULLSCREEN]: (state, action) => action.fullscreen
  }),
  data: makeReducer({}, {
    [TOOL_LOAD]: (state, action) => action.toolData.data || {}
  })
})

export {
  reducer
}
