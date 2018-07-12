import merge from 'lodash/merge'

import {makeReducer, combineReducers} from '#/main/app/store/reducer'

import {
  RESOURCE_UPDATE_NODE,
  USER_EVALUATION_UPDATE
} from '#/main/core/resource/store/actions'

const reducer = combineReducers({
  embedded: makeReducer(false), // this can not be changed at runtime

  /**
   * Manages the ResourceNode of the resource.
   */
  node: makeReducer({}, {
    /**
     * Updates the ResourceNode data.
     *
     * @param {object} state  - the current node data.
     * @param {object} action - the action. New node data is stored in `resourceNode`
     */
    [RESOURCE_UPDATE_NODE]: (state, action) => merge({}, state, action.resourceNode)
  }),

  /**
   * Manages current user's evaluation for the resource.
   */
  evaluation: makeReducer(null, {
    [USER_EVALUATION_UPDATE]: (state, action) => action.userEvaluation
  }),

  lifecycle: makeReducer({})
})


export {
  reducer
}
