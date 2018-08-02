import merge from 'lodash/merge'
import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'
import {LOAD_LOG, RESET_LOG, LOAD_CHART_DATA} from '#/main/core/layout/logs/actions'
import {LIST_FILTER_ADD, LIST_FILTER_REMOVE} from '#/main/app/content/list/store/actions'

const defaultState = {
  logs: {},
  userActions: {},
  log: {},
  actions: [],
  chart: {
    invalidated: true,
    data: {}
  }
}

/**
 * Creates reducers for logs.
 *
 * The `customReducers` param permits to pass reducers for specific logs actions.
 * `customReducers` are applied after the log ones.
 *
 * Example to add a custom reducer to `data`:
 *   customReducers = {
 *      workspaceId: handler
 *   }
 *
 * @param {object} initialState  - the initial state of the list instance (useful to add default filters in autoloading lists).
 * @param {object} customReducer - an object containing custom reducer.
 *
 * @returns {function}
 */
const makeLogReducer = (initialState = {}, customReducer = {}) => {
  const listState = merge({}, defaultState, initialState)
  const reducer = {
    logs: makeListReducer(
      'logs',
      { sortBy: { property: 'dateLog', direction: -1 } },
      {},
      { selectable: false }
    ),
    userActions: makeListReducer(
      'userActions',
      { sortBy: { property: 'doer.name', direction: 1 } },
      {},
      { selectable: false }
    ),
    log: makeReducer(listState.log, {
      [RESET_LOG]: (state, action) => action.log,
      [LOAD_LOG]: (state, action) => action.log
    }),
    actions: makeReducer(listState.actions, {}),
    chart: combineReducers({
      invalidated: makeReducer(listState.chart.invalidated, {
        [LIST_FILTER_ADD+'/logs'] : () => true,
        [LIST_FILTER_REMOVE+'/logs'] : () => true,
        [LOAD_CHART_DATA] : () => false
      }),
      data: makeReducer(listState.chart.data, {
        [LOAD_CHART_DATA]: (state, action) => action.data
      })
    })
  }

  return merge({}, customReducer, reducer)
}

export {
  makeLogReducer
}