import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'
import {RESOURCE_LOAD} from '#/main/core/resource/store/actions'

import {selectors as editorSelectors} from '#/plugin/url/resources/url/editor/store/selectors'
import {reducer as editorReducer} from '#/plugin/url/resources/url/editor/store/reducer'

const reducer = combineReducers({
  urlForm: editorReducer.urlForm,
  url: makeReducer({}, {
    [RESOURCE_LOAD]: (state, action) => action.resourceData.url,
    // replaces path data after success updates
    [FORM_SUBMIT_SUCCESS+'/'+editorSelectors.FORM_NAME]: (state, action) => action.updatedData
  })
})

export {
  reducer
}
