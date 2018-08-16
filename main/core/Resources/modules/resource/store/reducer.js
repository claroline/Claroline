import merge from 'lodash/merge'

import {makeReducer, combineReducers} from '#/main/app/store/reducer'

import {
  RESOURCE_LOAD,
  RESOURCE_UPDATE_NODE,
  USER_EVALUATION_UPDATE,
  RESOURCE_RESTRICTIONS_DISMISS,
  RESOURCE_RESTRICTIONS_ERROR
} from '#/main/core/resource/store/actions'

const reducer = {
  loaded: makeReducer(false, {
    [RESOURCE_LOAD]: () => true
  }),

  accessErrors: combineReducers({
    dismissed: makeReducer(false, {
      [RESOURCE_RESTRICTIONS_DISMISS]: () => true
    }),
    details: makeReducer({}, {
      [RESOURCE_LOAD]: (state, action) => action.resourceData.accessErrors || {},
      [RESOURCE_RESTRICTIONS_ERROR]: (state, action) => action.errors
    })
  }),

  embedded: makeReducer(false), // this can not be changed at runtime

  managed: makeReducer(false, {
    [RESOURCE_LOAD]: (state, action) => action.resourceData.managed || false
  }),

  /**
   * Manages the ResourceNode of the resource.
   */
  resourceNode: makeReducer({}, {
    [RESOURCE_LOAD]: (state, action) => action.resourceData.resourceNode,

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
  userEvaluation: makeReducer(null, {
    [RESOURCE_LOAD]: (state, action) => action.resourceData.userEvaluation,
    [USER_EVALUATION_UPDATE]: (state, action) => action.userEvaluation
  }),

  lifecycle: makeReducer({})
}


export {
  reducer
}
