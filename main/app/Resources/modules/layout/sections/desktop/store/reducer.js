import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {SECURITY_USER_CHANGE} from '#/main/app/security/store/actions'
import {
  DESKTOP_LOAD,
  DESKTOP_HISTORY_LOAD
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

  history: combineReducers({
    loaded: makeReducer(false, {
      [DESKTOP_HISTORY_LOAD]: () => true
    }),
    results: makeReducer([], {
      [DESKTOP_HISTORY_LOAD]: (state, action) => action.history
    })
  })
})

export {
  reducer
}
