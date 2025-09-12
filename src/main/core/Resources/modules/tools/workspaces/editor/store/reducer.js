import {combineReducers, makeReducer} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store/reducer'
import {TOOL_OPEN} from '#/main/core/tool/store/actions'

import {selectors} from '#/main/core/tools/workspaces/editor/store/selectors'

export const reducer = combineReducers({
  /**
   * The list of the models for workspace creation.
   */
  models: makeListReducer(selectors.MODELS_LIST_NAME, {
    sortBy: {property: 'updatedAt', direction: -1}
  }, {
    invalidated: makeReducer(false, {
      [TOOL_OPEN]: () => true
    })
  }),

  /**
   * The list of the archived workspaces.
   */
  archives: makeListReducer(selectors.ARCHIVES_LIST_NAME, {
    sortBy: {property: 'updatedAt', direction: -1}
  }, {
    invalidated: makeReducer(false, {
      [TOOL_OPEN]: () => true
    })
  })
})
