import {makeActionCreator} from '#/main/app/store/actions'
import {API_REQUEST} from '#/main/app/api'

export const RECORDINGS_LOAD = 'RECORDINGS_LOAD'

export const actions = {}

actions.loadRecordings = makeActionCreator(RECORDINGS_LOAD, 'recordings')

actions.fetchRecordings = (meetingId) => (dispatch) => dispatch({
  [API_REQUEST]: {
    url: ['apiv2_bbb_meeting_recordings_list', {id: meetingId}],
    request: {
      method: 'GET'
    },
    success: (data, dispatch) => dispatch(actions.loadRecordings(data))
  }
})

actions.deleteRecording = (meetingId, recordId) => (dispatch) => dispatch({
  [API_REQUEST]: {
    url: ['apiv2_bbb_meeting_recording_delete', {id: meetingId, recordId: recordId}],
    request: {
      method: 'DELETE'
    },
    success: (data, dispatch) => dispatch(actions.fetchRecordings(meetingId))
  }
})
