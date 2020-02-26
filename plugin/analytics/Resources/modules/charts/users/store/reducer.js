import {makeReducer, combineReducers} from '#/main/app/store/reducer'

import {
  USERS_CHART_LOAD,
  USERS_CHART_CHANGE_MODE
} from '#/plugin/analytics/charts/users/store/actions'

export const reducer = combineReducers({
  loaded: makeReducer(false, {
    [USERS_CHART_LOAD] : () => true
  }),
  mode: makeReducer('chart', {
    [USERS_CHART_CHANGE_MODE]: (state, action) => action.mode
  }),
  data: makeReducer([], {
    [USERS_CHART_LOAD]: (state, action) => action.data
  })
})
