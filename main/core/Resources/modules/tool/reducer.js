import difference from 'lodash/difference'

import {makeReducer} from '#/main/core/scaffolding/reducer'
import {makePageReducer} from '#/main/core/layout/page/reducer'

const baseReducer = {
  editable: makeReducer(false, {}),
  context: makeReducer({}, {})
}

/**
 * Creates reducers for tools.
 * It will register required reducers for tool features
 *
 * The `customReducers` param permits to pass reducers for specific tool implementation.
 *
 * @param {object} initialState  - the initial state of the tool instance.
 * @param {object} customReducer - an object containing custom reducer.
 *
 * @returns {function}
 */
function makeToolReducer(initialState = {}, customReducer = {}) {
  const toolReducer = Object.assign({}, baseReducer)

  // todo maybe make it customizable (like forms and lists)

  // get custom keys
  const rest = difference(Object.keys(customReducer), Object.keys(toolReducer))
  rest.map(reducerName =>
    toolReducer[reducerName] = customReducer[reducerName]
  )

  return makePageReducer(initialState, toolReducer, {
    modals: true,
    alerts: true
  })
}

export {
  makeToolReducer
}
