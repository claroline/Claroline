import cloneDeep from 'lodash/cloneDeep'

import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {SECURITY_USER_CHANGE} from '#/main/app/security/store/actions'
import {
  WORKSPACE_OPEN,
  WORKSPACE_LOAD,
  WORKSPACE_SET_LOADED,
  WORKSPACE_SERVER_ERRORS,
  WORKSPACE_RESTRICTIONS_ERROR,
  WORKSPACE_RESTRICTIONS_DISMISS,
  WORKSPACE_RESTRICTIONS_UNLOCKED,
  SHORTCUTS_LOAD,
  WORKSPACE_NOT_FOUND
} from '#/main/core/workspace/store/actions'

const reducer = combineReducers({
  loaded: makeReducer(false, {
    [SECURITY_USER_CHANGE]: () => false,
    [WORKSPACE_OPEN]: () => false,
    [WORKSPACE_SET_LOADED]: (state, action) => action.loaded
  }),
  notFound: makeReducer(false, {
    [SECURITY_USER_CHANGE]: () => false,
    [WORKSPACE_OPEN]: () => false,
    [WORKSPACE_NOT_FOUND]: () => true
  }),
  impersonated: makeReducer(false, {
    [WORKSPACE_OPEN]: () => false,
    [WORKSPACE_LOAD]: (state, action) => action.workspaceData.impersonated || false
  }),
  roles: makeReducer([], {
    [WORKSPACE_OPEN]: () => [],
    [WORKSPACE_LOAD]: (state, action) => action.workspaceData.roles || []
  }),
  managed: makeReducer(false, {
    [WORKSPACE_OPEN]: () => false,
    [WORKSPACE_LOAD]: (state, action) => action.workspaceData.managed || false
  }),
  workspace: makeReducer(null, {
    [WORKSPACE_OPEN]: () => null,
    [WORKSPACE_LOAD]: (state, action) => action.workspaceData.workspace
  }),
  tools: makeReducer([], {
    [WORKSPACE_OPEN]: () => [],
    [WORKSPACE_LOAD]: (state, action) => action.workspaceData.tools || []
  }),
  root: makeReducer({}, {
    [WORKSPACE_OPEN]: () => ({}),
    [WORKSPACE_LOAD]: (state, action) => action.workspaceData.root || {}
  }),
  shortcuts: makeReducer([], {
    [WORKSPACE_OPEN]: () => [],
    [WORKSPACE_LOAD]: (state, action) => action.workspaceData.shortcuts || [],
    [SHORTCUTS_LOAD]: (state, action) => action.shortcuts || []
  }),
  userEvaluation: makeReducer(null, {
    [WORKSPACE_OPEN]: () => null,
    [WORKSPACE_LOAD]: (state, action) => action.workspaceData.userEvaluation || state
  }),
  accessErrors: combineReducers({
    dismissed: makeReducer(false, {
      [WORKSPACE_OPEN]: () => false,
      [WORKSPACE_RESTRICTIONS_DISMISS]: () => true//,
      /*[WORKSPACE_LOAD]: (state, action) => {
       //+ date check and ips and the hidden flag most likely but I have no example now
       return action.resourceData.resourceNode.permissions.open &&
       !action.resourceData.accessErrors.notPublished &&
       !action.resourceData.accessErrors.deleted &&
       !action.resourceData.accessErrors.locked &&
       !action.resourceData.accessErrors.notStarted &&
       !action.resourceData.accessErrors.ended
       }*/
    }),
    details: makeReducer({}, {
      [WORKSPACE_OPEN]: () => ({}),
      [WORKSPACE_LOAD]: (state, action) => action.workspaceData.accessErrors || {},
      [WORKSPACE_RESTRICTIONS_ERROR]: (state, action) => action.errors,
      [WORKSPACE_RESTRICTIONS_UNLOCKED]: (state) => {
        const newState = cloneDeep(state)
        newState.locked = false
        return newState
      }
    })
  }),

  serverErrors: makeReducer([], {
    [WORKSPACE_OPEN]: () => [],
    [WORKSPACE_SERVER_ERRORS]: (state, action) => action.errors
  })
})

export {
  reducer
}
