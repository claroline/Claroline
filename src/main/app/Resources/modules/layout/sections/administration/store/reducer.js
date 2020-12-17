import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {
  ADMINISTRATION_LOAD
} from '#/main/app/layout/sections/administration/store/actions'

const reducer = combineReducers({
  loaded: makeReducer(false, {
    [ADMINISTRATION_LOAD]: () => true
  }),

  /**
   * The list of available tools on the administration.
   */
  tools: makeReducer([], {
    [ADMINISTRATION_LOAD]: (state, action) => action.tools || []
  })
})

export {
  reducer
}
