import {makeReducer} from '#/main/core/scaffolding/reducer'
import {makePageReducer} from '#/main/core/layout/page/reducer'

import {reducer as editorReducer} from '#/main/core/tools/home/editor/reducer'

const reducer = makePageReducer({}, {
  title: makeReducer(null, {}),
  editable: makeReducer(false, {}),
  context: makeReducer({}, {}),
  tabs: makeReducer([], {}),
  widgets: makeReducer([], {}),
  editor: editorReducer
})

export {
  reducer
}
