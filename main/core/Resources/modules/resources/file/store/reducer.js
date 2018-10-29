import {combineReducers, makeReducer} from '#/main/app/store/reducer'
import {reducer as editorReducer} from '#/main/core/resources/file/editor/store/reducer'
import {RESOURCE_LOAD} from '#/main/core/resource/store'

const reducer = combineReducers({
  fileForm: editorReducer.fileForm,
  file: makeReducer({}, {
    [RESOURCE_LOAD]: (state, action) => action.resourceData.file
  })
})

export {
  reducer
}
