import {makeInstanceAction} from '#/main/app/store/actions'
import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'
import {SEARCH_FILTER_ADD, SEARCH_FILTER_REMOVE} from '#/main/app/content/search/store/actions'

import {TOOL_LOAD} from '#/main/core/tool/store/actions'
import {LOAD_LOG, RESET_LOG, LOAD_CHART_DATA} from '#/main/core/layout/logs/actions'
import {LOAD_ANALYTICS, LOAD_REQUIREMENTS} from '#/plugin/analytics/tools/dashboard/store/actions'
import {selectors} from '#/plugin/analytics/tools/dashboard/store/selectors'
import {reducer as pathReducer} from '#/plugin/analytics/tools/dashboard/path/store/reducer'

const reducer = combineReducers({
  logs: makeListReducer(selectors.STORE_NAME + '.logs', {
    sortBy: { property: 'dateLog', direction: -1 }
  }, {
    invalidated: makeReducer(false, {
      [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: () => true
    })
  }),
  userActions: makeListReducer(selectors.STORE_NAME + '.userActions', {
    sortBy: { property: 'doer.name', direction: 1 }
  }, {
    invalidated: makeReducer(false, {
      [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: () => true
    })
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
  analytics: combineReducers({
    loaded: makeReducer(false, {
      [LOAD_ANALYTICS] : () => true
    }),
    data: makeReducer({}, {
      [LOAD_ANALYTICS]: (state, action) => action.data
    })
  }),
  connections: combineReducers({
    list: makeListReducer(selectors.STORE_NAME + '.connections.list', {
      sortBy: {property: 'connectionDate', direction: -1}
    }, {
      invalidated: makeReducer(false, {
        [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: () => true
      })
    })
  }),
  items: makeReducer([], {
    [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: (state, action) => action.toolData.items
  }),
  levelMax: makeReducer(null, {
    [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: (state, action) => action.toolData.levelMax
  }),
  nbConnections: makeReducer(null, {
    [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: (state, action) => action.toolData.nbConnections
  }),
  path: pathReducer,
  requirements: combineReducers({
    roles: makeListReducer(selectors.STORE_NAME + '.requirements.roles', {}, {
      invalidated: makeReducer(false, {
        [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: () => true
      })
    }),
    users: makeListReducer(selectors.STORE_NAME + '.requirements.users', {}, {
      invalidated: makeReducer(false, {
        [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: () => true
      })
    }),
    current: makeReducer(null, {
      [LOAD_REQUIREMENTS]: (state, action) => action.data
    })
  }),
  evaluations: makeListReducer(selectors.STORE_NAME + '.evaluations', {}, {
    invalidated: makeReducer(false, {
      [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: () => true
    })
  })
})

export {
  reducer
}
