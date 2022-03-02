import {makeReducer, combineReducers} from '#/main/app/store/reducer'

import {SECURITY_USER_CHANGE} from '#/main/app/security/store/actions'
import {
  TAB_LOAD,
  TAB_SET_LOADED,
  TAB_RESTRICTIONS_DISMISS
} from '#/plugin/home/tools/home/player/store/actions'

const reducer = combineReducers({
  loaded: makeReducer(false, {
    [SECURITY_USER_CHANGE]: () => false,
    [TAB_LOAD]: () => true,
    [TAB_SET_LOADED]: (state, action) => action.loaded
  }),

  current: makeReducer(null, {
    [TAB_LOAD]: (state, action) => action.homeTab
  }),

  managed: makeReducer(false, {
    [TAB_LOAD]: (state, action) => action.managed || false
  }),

  accessErrors: combineReducers({
    dismissed: makeReducer(false, {
      [TAB_RESTRICTIONS_DISMISS]: () => true,
      [TAB_LOAD]: () => false
    }),
    details: makeReducer({}, {
      [TAB_LOAD]: (state, action) => action.accessErrors || {}
    })
  })
})

export {
  reducer
}
