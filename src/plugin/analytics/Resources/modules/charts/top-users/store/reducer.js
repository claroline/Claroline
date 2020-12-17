import {makeReducer, combineReducers} from '#/main/app/store/reducer'

import {TOP_USERS_LOAD} from '#/plugin/analytics/charts/top-users/store/actions'

export const reducer = combineReducers({
  loaded: makeReducer(false, {
    [TOP_USERS_LOAD]: () => true
  }),
  data: makeReducer([], {
    [TOP_USERS_LOAD]: (state, action) => action.data
  })
})
