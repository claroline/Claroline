import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {SECURITY_USER_CHANGE} from '#/main/app/security/store/actions'
import {
  CONTEXT_OPEN,
  CONTEXT_LOAD,
  CONTEXT_NOT_FOUND,
  CONTEXT_RESTRICTIONS_DISMISS,
  CONTEXT_SHORTCUTS_LOAD,
  CONTEXT_SET_LOADED
} from '#/main/app/context/store/actions'

const reducer = combineReducers({
  /**
   * The type of the current context.
   *
   * @var string
   */
  type: makeReducer(null, {
    [CONTEXT_OPEN]: (state, action) => action.contextType
  }),

  /**
   * The optional identifier of the context.
   *
   * @var string
   */
  id: makeReducer(null, {
    [CONTEXT_OPEN]: (state, action) => action.contextId
  }),

  /**
   * Are the context data fully loaded ?
   */
  loaded: makeReducer(false, {
    [SECURITY_USER_CHANGE]: () => false,
    [CONTEXT_OPEN]: () => false,
    [CONTEXT_LOAD]: () => true,
    [CONTEXT_NOT_FOUND]: () => true,
    [CONTEXT_SET_LOADED]: (state, action) => action.loaded
  }),
  notFound: makeReducer(false, {
    [CONTEXT_OPEN]: () => false,
    [CONTEXT_NOT_FOUND]: () => true
  }),
  accessErrors: combineReducers({
    dismissed: makeReducer(false, {
      [CONTEXT_RESTRICTIONS_DISMISS]: () => true,
      [CONTEXT_OPEN]: () => false
    }),
    details: makeReducer({}, {
      [CONTEXT_LOAD]: (state, action) => action.contextData.accessErrors || {}
    })
  }),

  data: makeReducer({}, {
    [CONTEXT_LOAD]: (state, action) => action.contextData.data || {}
  }),

  impersonated: makeReducer(false, {
    [CONTEXT_OPEN]: () => false,
    [CONTEXT_LOAD]: (state, action) => action.contextData.impersonated || false
  }),

  roles: makeReducer([], {
    [CONTEXT_OPEN]: () => [],
    [CONTEXT_LOAD]: (state, action) => action.contextData.roles || []
  }),

  managed: makeReducer(false, {
    [CONTEXT_OPEN]: () => false,
    [CONTEXT_LOAD]: (state, action) => action.contextData.managed || false
  }),

  /**
   * The list of available tools on the desktop.
   */
  tools: makeReducer([], {
    [CONTEXT_LOAD]: (state, action) => action.contextData.tools || []
  }),

  /**
   * The list of shortcuts to tools or actions.
   */
  shortcuts: makeReducer([], {
    [CONTEXT_LOAD]: (state, action) => action.contextData.shortcuts || [],
    [CONTEXT_SHORTCUTS_LOAD]: (state, action) => action.shortcuts || []
  })
})

export {
  reducer
}
