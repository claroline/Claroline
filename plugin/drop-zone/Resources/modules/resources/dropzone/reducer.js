import {makeReducer} from '#/main/app/store/reducer'

import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'

// app reducers
import {reducer as editorReducer} from '#/plugin/drop-zone/resources/dropzone/editor/reducer'
import {reducer as playerReducer} from '#/plugin/drop-zone/resources/dropzone/player/reducer'
import {reducer as correctionReducer} from '#/plugin/drop-zone/resources/dropzone/correction/reducer'
import {reducer as configurationReducer} from '#/plugin/drop-zone/plugin/configuration/reducer'

const dropzoneReducer = makeReducer({}, {
  // replaces dropzone data after success updates
  [FORM_SUBMIT_SUCCESS+'/dropzoneForm']: (state, action) => action.updatedData
})

const reducer = {
  user: makeReducer({}, {}),
  dropzone: dropzoneReducer,
  dropzoneForm: editorReducer,
  myDrop: playerReducer.myDrop,
  peerDrop: playerReducer.peerDrop,
  nbCorrections: playerReducer.nbCorrections,
  drops: correctionReducer.drops,
  currentDrop: correctionReducer.currentDrop,
  correctorDrop: correctionReducer.correctorDrop,
  corrections: correctionReducer.corrections,
  tools: configurationReducer.tools,
  userEvaluation: makeReducer({}, {}),
  teams: makeReducer({}, {}),
  errorMessage: makeReducer({}, {})
}

export {
  reducer
}
