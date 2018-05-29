import {makeReducer} from '#/main/core/scaffolding/reducer'

import {reducer as editorReducer} from '#/main/core/tools/home/editor/reducer'

const reducer = {
  title: makeReducer(null, {}),
  tabs: makeReducer([], {}),
  widgets: makeReducer([], {}),
  editor: editorReducer
}

export {
  reducer
}
