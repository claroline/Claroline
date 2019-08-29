import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {SECURITY_USER_CHANGE} from '#/main/app/security/store/actions'
import {
  DESKTOP_LOAD
} from '#/main/app/layout/sections/desktop/store/actions'

const reducer = combineReducers({
  loaded: makeReducer(false, {
    [SECURITY_USER_CHANGE]: () => false,
    [DESKTOP_LOAD]: () => true
  }),

  /**
   * The list of available tools on the desktop.
   */
  tools: makeReducer([], {
    [DESKTOP_LOAD]: (state, action) => action.tools || []
  }),

  /**
   * The current user progression.
   */
  userProgression: makeReducer(null, {
    [DESKTOP_LOAD]: (state, action) => action.userProgression || null
  }),

  /**
   * The list of shortcuts to tools or actions.
   */
  shortcuts: makeReducer(null, {
    [DESKTOP_LOAD]: (state, action) => action.shortcuts || []
  })
})

export {
  reducer
}
