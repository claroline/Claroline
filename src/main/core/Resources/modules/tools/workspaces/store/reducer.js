import {makeInstanceAction} from '#/main/app/store/actions'
import {combineReducers, makeReducer} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store/reducer'

import {TOOL_LOAD, TOOL_OPEN} from '#/main/core/tool/store/actions'
import {CONTEXT_OPEN} from '#/main/app/context/store/actions'

export const reducer = combineReducers({
  /**
   * Current configuration of the tool.
   */
  parameters: makeReducer({}),

  /**
   * Does the current user can create new workspaces ?
   */
  creatable: makeReducer(false, {
    [makeInstanceAction(TOOL_LOAD, 'workspaces')]: (state, action) => action.toolData.creatable
  }),

  /**
   * The list of workspaces in which the current user is registered.
   */
  registered: makeListReducer('workspaces.registered', {
    sortBy: {property: 'createdAt', direction: -1}
  }, {
    loaded: makeReducer(false, {
      [CONTEXT_OPEN]: () => false
    }),
    invalidated: makeReducer(false, {
      [TOOL_OPEN]: () => true
    })
  }),

  /**
   * The list of the platform public workspaces.
   */
  public: makeListReducer('workspaces.public', {
    sortBy: {property: 'createdAt', direction: -1}
  }, {
    loaded: makeReducer(false, {
      [CONTEXT_OPEN]: () => false
    }),
    invalidated: makeReducer(false, {
      [TOOL_OPEN]: () => true
    })
  })
})
