import {combineReducers, makeReducer} from '#/main/app/store/reducer'
import {makeInstanceAction} from '#/main/app/store/actions'
import {RESOURCE_LOAD} from '#/main/core/resource/store/actions'

import {MEETING_STATUS_UPDATE} from '#/integration/big-blue-button/resources/bbb/store/actions'
import {selectors} from '#/integration/big-blue-button/resources/bbb/store/selectors'
import {reducer as recordsReducer} from '#/integration/big-blue-button/resources/bbb/records/store/reducer'

const reducer = combineReducers({
  resource: makeReducer({}, {
    [makeInstanceAction(RESOURCE_LOAD, selectors.STORE_NAME)]: (state, action) => action.resourceData.resource || state,
  }),

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
  lastRecording: makeReducer(null, {
    [makeInstanceAction(RESOURCE_LOAD, selectors.STORE_NAME)]: (state, action) => action.resourceData.lastRecording || state
  }),
  recordings: recordsReducer
})

export {
  reducer
}