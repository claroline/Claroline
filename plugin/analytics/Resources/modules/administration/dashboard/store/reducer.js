import {makeInstanceAction} from '#/main/app/store/actions'
import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'
import {SEARCH_FILTER_ADD, SEARCH_FILTER_REMOVE} from '#/main/app/content/search/store/actions'

import {TOOL_LOAD} from '#/main/core/tool/store/actions'
import {LOAD_LOG, RESET_LOG, LOAD_CHART_DATA} from '#/main/core/layout/logs/actions'
import {
  LOAD_OVERVIEW,
  LOAD_AUDIENCE,
  LOAD_RESOURCES,
  LOAD_WIDGETS
} from '#/plugin/analytics/administration/dashboard/store/actions'
import {selectors} from '#/plugin/analytics/administration/dashboard/store/selectors'

const reducer = combineReducers({
  logs: makeListReducer(selectors.STORE_NAME + '.logs', {
    sortBy: { property: 'dateLog', direction: -1 }
  }),
  userActions: makeListReducer(selectors.STORE_NAME + '.userActions', {
    sortBy: { property: 'doer.name', direction: 1 }
  }),
  log: makeReducer({}, {
    [RESET_LOG]: (state, action) => action.log,
    [LOAD_LOG]: (state, action) => action.log
  }),
  actions: makeReducer([], {
    [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: (state, action) => action.toolData.actions
  }),
  chart: combineReducers({
    invalidated: makeReducer(true, {
      [SEARCH_FILTER_ADD + '/' + selectors.STORE_NAME + '.logs'] : () => true,
      [SEARCH_FILTER_REMOVE + '/' + selectors.STORE_NAME + '.logs'] : () => true,
      [LOAD_CHART_DATA] : () => false
    }),
    data: makeReducer({}, {
      [LOAD_CHART_DATA]: (state, action) => action.data
    })
  }),
  overview: combineReducers({
    loaded: makeReducer(false, {
      [LOAD_OVERVIEW] : () => true
    }),
    data: makeReducer({}, {
      [LOAD_OVERVIEW]: (state, action) => action.data
    })
  }),
  audience: combineReducers({
    loaded: makeReducer(false, {
      [LOAD_AUDIENCE] : () => true
    }),
    data: makeReducer({}, {
      [LOAD_AUDIENCE]: (state, action) => action.data
    })
  }),
  resources: combineReducers({
    loaded: makeReducer(false, {
      [LOAD_RESOURCES] : () => true
    }),
    data: makeReducer({}, {
      [LOAD_RESOURCES]: (state, action) => action.data
    })
  }),
  widgets: combineReducers({
    loaded: makeReducer(false, {
      [LOAD_WIDGETS] : () => true
    }),
    data: makeReducer({}, {
      [LOAD_WIDGETS]: (state, action) => action.data
    })
  }),
  topActions: makeListReducer(selectors.STORE_NAME + '.topActions', {
    filters: [{property: 'type', value: 'top_users_connections'}]
  }),
  connections: combineReducers({
    list: makeListReducer(selectors.STORE_NAME + '.connections.list', {
      sortBy: {property: 'connectionDate', direction: -1}
    })
  })
})

export {reducer}