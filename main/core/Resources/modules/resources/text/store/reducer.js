import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {makeInstanceAction} from '#/main/app/store/actions'
import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'

import {RESOURCE_LOAD} from '#/main/core/resource/store/actions'
import {selectors as editorSelectors} from '#/main/core/resources/text/editor/store/selectors'
import {reducer as editorReducer} from '#/main/core/resources/text/editor/store/reducer'

const reducer = combineReducers({
  textForm: editorReducer.textForm,
  text: makeReducer({}, {
    [makeInstanceAction(RESOURCE_LOAD, 'text')]: (state, action) => action.resourceData.text,
    // replaces path data after success updates
    [FORM_SUBMIT_SUCCESS+'/'+editorSelectors.FORM_NAME]: (state, action) => action.updatedData
  })
})

export {
  reducer
}