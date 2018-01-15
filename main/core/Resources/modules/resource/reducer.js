import difference from 'lodash/difference'
import merge from 'lodash/merge'

import {makeReducer} from '#/main/core/scaffolding/reducer'
import {makePageReducer} from '#/main/core/layout/page/reducer'

import {
  RESOURCE_UPDATE_PUBLICATION,
  RESOURCE_UPDATE_NODE
} from './actions'

const reducer = makeReducer({}, {
  /**
   * Toggles the publication status of a ResourceNode.
   */
  [RESOURCE_UPDATE_PUBLICATION]: (state) => merge({}, state, {
    meta: {
      published: !state.meta.published
    }
  }),

  /**
   * Updates the ResourceNode data.
   *
   * @param {object} state  - the current node data.
   * @param {object} action - the action. New node data is stored in `resourceNode`
   */
  [RESOURCE_UPDATE_NODE]: (state, action) => merge({}, state, action.resourceNode)
})

/**
 * Creates reducers for resources.
 * It will register required reducers for resource features
 *
 * The `customReducers` param permits to pass reducers for specific resource implementation.
 *
 * @param {object} initialState  - the initial state of the resource instance.
 * @param {object} customReducer - an object containing custom reducer.
 *
 * @returns {function}
 */
function makeResourceReducer(initialState = {}, customReducer = {}) {
  const resourceReducer = {}

  resourceReducer.resourceNode = reducer

  // get custom keys
  const rest = difference(Object.keys(customReducer), ['resourceNode'])
  rest.map(reducerName =>
    resourceReducer[reducerName] = customReducer[reducerName]
  )

  return makePageReducer(initialState, resourceReducer, {
    modals: true,
    alerts: true
  })
}

export {
  makeResourceReducer
}
