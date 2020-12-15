import {makeReducer, combineReducers} from '#/main/app/store/reducer'

import {
  ACTIVITY_CHART_LOAD
} from '#/plugin/analytics/charts/activity/store/actions'

export const reducer = combineReducers({
  loaded: makeReducer(false, {
    [ACTIVITY_CHART_LOAD] : () => true
  }),
  data: makeReducer({}, {
    [ACTIVITY_CHART_LOAD]: (state, action) => action.data
  })
})
