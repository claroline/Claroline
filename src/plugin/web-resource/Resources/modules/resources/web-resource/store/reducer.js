import {makeInstanceAction} from '#/main/app/store/actions'
import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {RESOURCE_LOAD} from '#/main/core/resource/store'

import {selectors} from '#/plugin/web-resource/resources/web-resource/store/selectors'
import {reducer as editorReducer} from '#/plugin/web-resource/resources/web-resource/editor/store/reducer'

const reducer = combineReducers({
  path: makeReducer({}, {
    [makeInstanceAction(RESOURCE_LOAD, selectors.STORE_NAME)]: (state, action) => action.resourceData.path
  }),
  file: makeReducer({}, {
    [makeInstanceAction(RESOURCE_LOAD, selectors.STORE_NAME)]: (state, action) => action.resourceData.file
  }),
  webResourceForm: editorReducer
})

export {
  reducer
}
