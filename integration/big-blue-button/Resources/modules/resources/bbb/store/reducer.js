import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {makeInstanceAction} from '#/main/app/store/actions'
import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'

import {RESOURCE_LOAD} from '#/main/core/resource/store/actions'

import {
  MEETING_STATUS_UPDATE
} from '#/integration/big-blue-button/resources/bbb/store/actions'
import {selectors} from '#/integration/big-blue-button/resources/bbb/store/selectors'
import {reducer as editorReducer} from '#/integration/big-blue-button/resources/bbb/editor/store/reducer'
import {reducer as recordsReducer} from '#/integration/big-blue-button/resources/bbb/records/store/reducer'

const reducer = combineReducers({
  servers: makeReducer([], {
    [makeInstanceAction(RESOURCE_LOAD, selectors.STORE_NAME)]: (state, action) => action.resourceData.servers || state
  }),
  // are records allowed in the platform ? (not to confuse with option to enable records on a bbb room)
  allowRecords: makeReducer(null, {
    [makeInstanceAction(RESOURCE_LOAD, selectors.STORE_NAME)]: (state, action) => action.resourceData.allowRecords || state
  }),
  canStart: makeReducer(null, {
    [makeInstanceAction(RESOURCE_LOAD, selectors.STORE_NAME)]: (state, action) => action.resourceData.canStart || state
  }),
  joinStatus: makeReducer(null, {
    [makeInstanceAction(RESOURCE_LOAD, selectors.STORE_NAME)]: (state, action) => action.resourceData.joinStatus || state,
    [MEETING_STATUS_UPDATE]: (state, action) => action.status
  }),
  bbb: makeReducer({}, {
    [makeInstanceAction(RESOURCE_LOAD, selectors.STORE_NAME)]: (state, action) => action.resourceData.bbb || state,
    // replaces bbb data after success updates
    [FORM_SUBMIT_SUCCESS+'/'+selectors.STORE_NAME+'.bbbForm']: (state, action) => action.updatedData
  }),
  lastRecording: makeReducer(null, {
    [makeInstanceAction(RESOURCE_LOAD, selectors.STORE_NAME)]: (state, action) => action.resourceData.lastRecording || state
  }),

  bbbForm: editorReducer,
  recordings: recordsReducer
})

export {
  reducer
}