import {makeReducer, combineReducers} from '#/main/app/store/reducer'

import {
  RESOURCES_CHART_LOAD,
  RESOURCES_CHART_CHANGE_MODE
} from '#/plugin/analytics/charts/resources/store/actions'

export const reducer = combineReducers({
  loaded: makeReducer(false, {
    [RESOURCES_CHART_LOAD] : () => true
  }),
  mode: makeReducer('chart', {
    [RESOURCES_CHART_CHANGE_MODE]: (state, action) => action.mode
  }),
  data: makeReducer({}, {
    [RESOURCES_CHART_LOAD]: (state, action) => action.data
  })
})
