import {combineReducers, makeReducer} from '#/main/app/store/reducer'
import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'
import {RESOURCE_LOAD} from '#/main/core/resource/store/actions'

import {selectors as editorSelectors, reducer as editorReducer} from '#/plugin/slideshow/resources/slideshow/editor/store'

const reducer = combineReducers(Object.assign({
  slideshow: makeReducer({}, {
    [RESOURCE_LOAD]: (state, action) => action.resourceData.slideshow || state,
    [FORM_SUBMIT_SUCCESS+'/'+editorSelectors.FORM_NAME]: (state, action) => action.updatedData
  })
}, editorReducer))

export {
  reducer
}
