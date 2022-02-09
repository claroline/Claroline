import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {SECURITY_USER_CHANGE} from '#/main/app/security/store/actions'
import {
  WORKSPACE_OPEN,
  WORKSPACE_LOAD,
  WORKSPACE_SET_LOADED,
  WORKSPACE_RESTRICTIONS_DISMISS,
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
      [WORKSPACE_RESTRICTIONS_DISMISS]: () => true,
      [WORKSPACE_LOAD]: () => false
    }),
    details: makeReducer({}, {
      [WORKSPACE_LOAD]: (state, action) => action.workspaceData.accessErrors || {}
    })
  })
})

export {
  reducer
}
