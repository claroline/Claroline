import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'
import {RESOURCE_LOAD} from '#/main/core/resource/store/actions'

import {selectors as editorSelectors} from '#/main/core/resources/directory/editor/store/selectors'
import {reducer as editorReducer} from '#/main/core/resources/directory/editor/store/reducer'
import {reducer as playerReducer} from '#/main/core/resources/directory/player/store/reducer'

const reducer = combineReducers({
  directoryExplorer: playerReducer.directoryExplorer,
  directoryForm: editorReducer.directoryForm,
  directory: makeReducer({}, {
    [RESOURCE_LOAD]: (state, action) => action.resourceData.directory,
    // replaces directory data after success updates
    [FORM_SUBMIT_SUCCESS+'/'+editorSelectors.FORM_NAME]: (state, action) => action.updatedData
  })
})

export {
  reducer
}