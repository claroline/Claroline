import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'
import {RESOURCE_LOAD} from '#/main/core/resource/store/actions'

// app reducers
import {reducer as editorReducer} from '#/plugin/drop-zone/resources/dropzone/editor/reducer'
import {reducer as playerReducer} from '#/plugin/drop-zone/resources/dropzone/player/reducer'
import {reducer as correctionReducer} from '#/plugin/drop-zone/resources/dropzone/correction/reducer'

const reducer = combineReducers({
  dropzone: makeReducer({}, {
    [RESOURCE_LOAD]: (state, action) => action.resourceData.dropzone,
    // replaces dropzone data after success updates
    [FORM_SUBMIT_SUCCESS+'/resource.dropzoneForm']: (state, action) => action.updatedData
  }),
  dropzoneForm: editorReducer,
  myDrop: playerReducer.myDrop,
  peerDrop: playerReducer.peerDrop,
  nbCorrections: playerReducer.nbCorrections,
  drops: correctionReducer.drops,
  currentDrop: correctionReducer.currentDrop,
  correctorDrop: correctionReducer.correctorDrop,
  corrections: correctionReducer.corrections,
  tools: makeReducer([], {
    [RESOURCE_LOAD]: (state, action) => action.resourceData.tools
  }),
  teams: makeReducer({}, {
    [RESOURCE_LOAD]: (state, action) => action.resourceData.teams
  }),
  errorMessage: makeReducer({}, {
    [RESOURCE_LOAD]: (state, action) => action.resourceData.errorMessage
  })
})

export {
  reducer
}
