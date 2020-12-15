import {makeReducer, combineReducers} from '#/main/app/store/reducer'

import {USER_LOAD_EXTERNAL_ACCOUNTS} from '#/main/core/tools/parameters/external/store/actions'

export const reducer = combineReducers({
  accounts: makeReducer([], {
    [USER_LOAD_EXTERNAL_ACCOUNTS]: (state, action) => action.data
  }),
  loaded: makeReducer(false, {
    [USER_LOAD_EXTERNAL_ACCOUNTS]: () => true
  })
})