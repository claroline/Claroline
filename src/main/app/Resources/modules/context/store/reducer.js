import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {SECURITY_USER_CHANGE} from '#/main/app/security/store/actions'
import {
  CONTEXT_OPEN,
  CONTEXT_LOAD,
  CONTEXT_NOT_FOUND,
  CONTEXT_RESTRICTIONS_DISMISS,
  CONTEXT_SET_LOADED,
  CONTEXT_MENU_CLOSE,
  CONTEXT_MENU_OPEN,
  CONTEXT_MENU_TOGGLE
} from '#/main/app/context/store/actions'
import get from 'lodash/get'
import cloneDeep from 'lodash/cloneDeep'
import {TOOL_LOAD} from '#/main/core/tool/store'

const reducer = combineReducers({
  menu: combineReducers({
    untouched: makeReducer(true, {
      [CONTEXT_MENU_OPEN]: () => false,
      [CONTEXT_MENU_CLOSE]: () => false,
      [CONTEXT_MENU_TOGGLE]: () => false
    }),
    opened: makeReducer(true, {
      [CONTEXT_MENU_OPEN]: () => true,
      [CONTEXT_MENU_CLOSE]: () => false,
      [CONTEXT_MENU_TOGGLE]: (state) => !state,
      [CONTEXT_LOAD]: (state, action) => {
        const menuState = get(action.contextData, 'data.opening.menu')
        if ('open' === menuState) {
          return true
        } else if ('close' === menuState) {
          return false
        }

        return state
      }
    })
  }),

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
