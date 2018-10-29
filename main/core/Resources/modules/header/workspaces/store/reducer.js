import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {WORKSPACES_MENU_LOAD} from '#/main/core/header/workspaces/store/actions'

const reducer = combineReducers({
  /**
   * The personal WS for the current user if any.
   */
  personal: makeReducer(null, {
    [WORKSPACES_MENU_LOAD]: (state, action) => action.personal
  }),
  /**
   * The last opened WS by the current user
   */
  history: makeReducer([], {
    [WORKSPACES_MENU_LOAD]: (state, action) => action.history
  }),

  /**
   * Does the current user can create new workspaces ?
   */
  creatable: makeReducer(false, {
    [WORKSPACES_MENU_LOAD]: (state, action) => action.creatable
  })
})

export {
  reducer
}
