import {makeInstanceAction} from '#/main/app/store/actions'
import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {RESOURCE_LOAD} from '#/main/core/resource/store/actions'

// app reducers
import {selectors} from '#/plugin/drop-zone/resources/dropzone/store/selectors'
import {reducer as playerReducer} from '#/plugin/drop-zone/resources/dropzone/player/store/reducer'
import {reducer as correctionReducer} from '#/plugin/drop-zone/resources/dropzone/correction/reducer'

const reducer = combineReducers({
  resource: makeReducer({}, {
    [makeInstanceAction(RESOURCE_LOAD, selectors.STORE_NAME)]: (state, action) => action.resourceData.resource,
  }),
  myDrop: playerReducer.myDrop,
  peerDrop: playerReducer.peerDrop,
  nbCorrections: playerReducer.nbCorrections,
  drops: correctionReducer.drops,
  currentDrop: correctionReducer.currentDrop,
  correctorDrop: correctionReducer.correctorDrop,
  corrections: correctionReducer.corrections,
  teams: makeReducer({}, {
    [makeInstanceAction(RESOURCE_LOAD, selectors.STORE_NAME)]: (state, action) => action.resourceData.teams
  }),
  errorMessage: makeReducer({}, {
    [makeInstanceAction(RESOURCE_LOAD, selectors.STORE_NAME)]: (state, action) => action.resourceData.errorMessage
  }),
  myRevisions: playerReducer.myRevisions,
  revisions: playerReducer.revisions,
  revision: playerReducer.revision,
  currentRevisionId: playerReducer.currentRevisionId
})

export {
  reducer
}
