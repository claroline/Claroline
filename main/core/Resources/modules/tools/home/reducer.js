import {makeReducer} from '#/main/core/scaffolding/reducer'

import {reducer as editorReducer} from '#/main/core/tools/home/editor/reducer'
import {
  CURRENT_TAB
} from '#/main/core/tools/home/actions'

const reducer = {
  currentTabId: makeReducer(null, {
    [CURRENT_TAB]: (state, action) => action.tab
  }),
  tabs: makeReducer([], {}),
  editor: editorReducer
}

export {
  reducer
}
