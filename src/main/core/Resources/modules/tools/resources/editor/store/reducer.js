import {combineReducers, makeReducer} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store/reducer'
import {TOOL_OPEN} from '#/main/core/tool/store/actions'

import {selectors} from '#/main/core/tools/resources/editor/store/selectors'

export const reducer = combineReducers({
  /**
   * The list of the archived resources.
   */
  archives: makeListReducer(selectors.ARCHIVES_LIST_NAME, {
    sortBy: {property: 'modificationDate', direction: -1}
  }, {
    invalidated: makeReducer(false, {
      [TOOL_OPEN]: () => true
    })
  })
})
