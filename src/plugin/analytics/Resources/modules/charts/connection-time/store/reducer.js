import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'

import {
  CONNECTION_TIME_CHART_LOAD
} from '#/plugin/analytics/charts/connection-time/store/actions'
import {selectors} from '#/plugin/analytics/charts/connection-time/store/selectors'

export const reducer = combineReducers({
  loaded: makeReducer(false, {
    [CONNECTION_TIME_CHART_LOAD] : () => true
  }),
  data: makeReducer({}, {
    [CONNECTION_TIME_CHART_LOAD]: (state, action) => action.data
  }),
  connections: makeListReducer(selectors.STORE_NAME + '.connections', {
    sortBy: {property: 'connectionDate', direction: -1}
  })
})
