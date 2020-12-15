import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store/reducer'

import {
  ACTIONS_CHART_LOAD
} from '#/plugin/analytics/charts/actions/store/actions'
import {selectors} from '#/plugin/analytics/charts/actions/store/selectors'

export const reducer = combineReducers({
  loaded: makeReducer(false, {
    [ACTIONS_CHART_LOAD] : () => true
  }),
  data: makeReducer({}, {
    [ACTIONS_CHART_LOAD]: (state, action) => action.data
  }),
  logs: makeListReducer(selectors.STORE_NAME + '.logs', {
    sortBy: { property: 'dateLog', direction: -1 }
  })
})
