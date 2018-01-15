import merge from 'lodash/merge'
import difference from 'lodash/difference'

import {combineReducers} from '#/main/core/scaffolding/reducer'

import {reducer as apiReducer} from '#/main/core/api/reducer'
import {reducer as alertReducer} from '#/main/core/layout/alert/reducer'
import {reducer as modalReducer} from '#/main/core/layout/modal/reducer'

import {constants} from '#/main/core/layout/page/constants'

const baseReducer = {
  modal: modalReducer,
  alerts: alertReducer
}

/**
 * Creates reducers for pages.
 * It will register reducers for enabled features (eg. alerts, modals)
 *
 * The `customReducers` param permits to pass reducers for specific page implementation.
 *
 * @param {object} initialState  - the initial state of the page instance.
 * @param {object} customReducer - an object containing custom reducer.
 * @param {object} options       - an options object to disable/enable page features (default: DEFAULT_FEATURES).
 *
 * @returns {function}
 */
function makePageReducer(initialState = {}, customReducer = {}, options = {}) {
  const reducer = {}

  //const pageState = merge({}, initialState) // todo use
  const pageOptions = merge({}, constants.DEFAULT_FEATURES, options)

  // add pages required reducers
  reducer.currentRequests = apiReducer

  if (pageOptions.modals) {
    reducer.modal = baseReducer.modal
  }

  if (pageOptions.alerts) {
    reducer.alerts = baseReducer.alerts
  }

  // get custom keys
  const rest = difference(Object.keys(customReducer), Object.keys(baseReducer))
  rest.map(reducerName =>
    reducer[reducerName] = customReducer[reducerName]
  )

  return combineReducers(reducer)
}

export {
  makePageReducer
}
