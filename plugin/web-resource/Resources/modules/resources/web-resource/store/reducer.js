import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {reducer as editorReducer} from '#/plugin/web-resource/resources/web-resource/editor/store/reducer'
import {RESOURCE_LOAD} from '#/main/core/resource/store'

const reducer = combineReducers({
  path: makeReducer({}, {
    [RESOURCE_LOAD]: (state, action) => action.resourceData.path
  }),
  file: makeReducer({}, {
    [RESOURCE_LOAD]: (state, action) => action.resourceData.file
  }),
  webResourceForm: editorReducer
})

export {
  reducer
}
