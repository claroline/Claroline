import {combineReducers, makeReducer} from '#/main/app/store/reducer'
import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'

import {reducer as editorReducer} from '#/main/core/resources/file/editor/store/reducer'
import {RESOURCE_LOAD} from '#/main/core/resource/store'
import {selectors} from '#/main/core/resources/file/store/selectors'

const reducer = combineReducers({
  fileForm: editorReducer.fileForm,
  file: makeReducer({}, {
    [RESOURCE_LOAD]: (state, action) => action.resourceData.file,
    // replaces file data after success updates
    [FORM_SUBMIT_SUCCESS+'/'+selectors.STORE_NAME+'.fileForm']: (state, action) => action.updatedData
  })
})

export {
  reducer
}
