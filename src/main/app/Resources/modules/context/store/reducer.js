import cloneDeep from 'lodash/cloneDeep'

import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {SECURITY_USER_CHANGE} from '#/main/app/security/store/actions'
import {
  CONTEXT_OPEN,
  CONTEXT_LOAD,
  CONTEXT_SET_LOADED,
  CONTEXT_MENU_TOGGLE_OPEN, CONTEXT_SET_ERROR
} from '#/main/app/context/store/actions'

import {TOOL_LOAD} from '#/main/core/tool/store'
import {PLATFORM_SET_CURRENT_ORGANIZATION} from '#/main/app/platform/store/actions'

const reducer = combineReducers({
  /**
   * The type of the current context (e.g., public, desktop, workspace, administration).
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
   * Is the context menu opened?
   * NB. we store the value inside the store to avoid the menu auto close when react render.
   */
  menuOpened: makeReducer(false, {
    [CONTEXT_MENU_TOGGLE_OPEN]: (state) => !state
  }),

  /**
   * Are the context data fully loaded?
   */
  loaded: makeReducer(false, {
    [SECURITY_USER_CHANGE]: () => false,
    [PLATFORM_SET_CURRENT_ORGANIZATION]: () => false,
    [CONTEXT_OPEN]: () => false,
    [CONTEXT_LOAD]: () => true,
    [CONTEXT_SET_ERROR]: () => true,
    [CONTEXT_SET_LOADED]: (state, action) => action.loaded
  }),
  error: makeReducer(null, {
    [SECURITY_USER_CHANGE]: () => null,
    [PLATFORM_SET_CURRENT_ORGANIZATION]: () => null,
    [CONTEXT_OPEN]: () => null,
    [CONTEXT_LOAD]: (state, action) => action.contextData.error || null,
    [CONTEXT_SET_ERROR]: (state, action) => ({
      code: action.code.toUpperCase(),
      message: action.message,
      additional: action.additional
    })
  }),

  data: makeReducer({}, {
    [CONTEXT_OPEN]: () => ({}),
    [CONTEXT_LOAD]: (state, action) => action.contextData.data || {}
  }),

  impersonated: makeReducer(false, {
    [CONTEXT_OPEN]: () => false,
    [CONTEXT_LOAD]: (state, action) => action.contextData.impersonated || false
  }),

  /**
   * The list of current context roles owned by the authenticated user.
   */
  roles: makeReducer([], {
    [CONTEXT_OPEN]: () => [],
    [CONTEXT_LOAD]: (state, action) => action.contextData.roles || []
  }),

  /**
   * The list of current context organizations owned by the authenticated user.
   */
  organizations: makeReducer([], {
    [CONTEXT_OPEN]: () => [],
    [CONTEXT_LOAD]: (state, action) => action.contextData.organizations || []
  }),

  /**
   * The list of available tools in the context.
   */
  tools: makeReducer([], {
    [CONTEXT_OPEN]: () => [],
    [CONTEXT_LOAD]: (state, action) => action.contextData.tools || [],
    [TOOL_LOAD]: (state, action) => {
      const toolPos = state.findIndex(tool => tool.name === action.toolName)
      if (-1 !== toolPos) {
        const newState = cloneDeep(state)
        newState[toolPos] = action.toolData.data

        return newState
      }

      return state
    }
  })
})

export {
  reducer
}
