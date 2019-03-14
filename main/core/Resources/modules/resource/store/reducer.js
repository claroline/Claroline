import merge from 'lodash/merge'
import cloneDeep from 'lodash/cloneDeep'

import {makeReducer, combineReducers} from '#/main/app/store/reducer'

import {
  RESOURCE_LOAD,
  RESOURCE_SET_LOADED,
  RESOURCE_UPDATE_NODE,
  USER_EVALUATION_UPDATE,
  RESOURCE_RESTRICTIONS_DISMISS,
  RESOURCE_RESTRICTIONS_ERROR,
  RESOURCE_SERVER_ERRORS,
  RESOURCE_RESTRICTIONS_UNLOCKED,
  RESOURCE_COMMENT_ADD,
  RESOURCE_COMMENT_UPDATE,
  RESOURCE_COMMENT_REMOVE
} from '#/main/core/resource/store/actions'

const reducer = {
  loaded: makeReducer(false, {
    [RESOURCE_SET_LOADED]: () => true
  }),

  accessErrors: combineReducers({
    dismissed: makeReducer(false, {
      [RESOURCE_RESTRICTIONS_DISMISS]: () => true,
      [RESOURCE_LOAD]: (state, action) => {
        //+ date check and ips and the hidden flag most likely but I have no example now
        return action.resourceData.resourceNode.permissions.open &&
          !action.resourceData.accessErrors.notPublished &&
          !action.resourceData.accessErrors.deleted &&
          !action.resourceData.accessErrors.locked &&
          !action.resourceData.accessErrors.notStarted &&
          !action.resourceData.accessErrors.ended
      }
    }),
    details: makeReducer({}, {
      [RESOURCE_LOAD]: (state, action) => action.resourceData.accessErrors || {},
      [RESOURCE_RESTRICTIONS_ERROR]: (state, action) => action.errors,
      [RESOURCE_RESTRICTIONS_UNLOCKED]: (state) => {
        const newState = cloneDeep(state)
        newState.locked = false
        return newState
      }
    })
  }),

  serverErrors: makeReducer([], {
    [RESOURCE_SERVER_ERRORS]: (state, action) => action.errors
  }),

  embedded: makeReducer(false), // this can not be changed at runtime

  showHeader: makeReducer(true),

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
    [RESOURCE_UPDATE_NODE]: (state, action) => merge({}, state, action.resourceNode),
    [RESOURCE_COMMENT_ADD]: (state, action) => {
      const newState = cloneDeep(state)
      const comment = newState['comments'].find(c => c.id === action.comment.id)

      if (!comment) {
        newState['comments'].unshift(action.comment)
      }

      return newState
    },
    [RESOURCE_COMMENT_UPDATE]: (state, action) => {
      const newState = cloneDeep(state)
      const index = newState['comments'].findIndex(c => c.id === action.comment.id)

      if (index > -1) {
        newState['comments'][index] = action.comment
      }

      return newState
    },
    [RESOURCE_COMMENT_REMOVE]: (state, action) => {
      const newState = cloneDeep(state)
      const index = newState['comments'].findIndex(c => c.id === action.commentId)

      if (index > -1) {
        newState['comments'].splice(index, 1)
      }

      return newState
    }
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
