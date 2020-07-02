import {makeActionCreator} from '#/main/app/store/actions'
import {API_REQUEST} from '#/main/app/api'

export const MEETING_STATUS_UPDATE = 'MEETING_STATUS_UPDATE'

const actions = {}

actions.updateMeetingStatus = makeActionCreator(MEETING_STATUS_UPDATE, 'status')

actions.createMeeting = (bbb) => (dispatch) => dispatch({
  [API_REQUEST]: {
    url: ['apiv2_bbb_meeting_create', {id: bbb.id}],
    silent: true,
    request: {
      method: 'POST'
    },
    success: (response) => dispatch(actions.updateMeetingStatus(response.joinStatus))
  }
})

actions.endMeeting = (meetingId) => (dispatch) => dispatch({
  [API_REQUEST]: {
    url: ['apiv2_bbb_meeting_end', {id: meetingId}],
    request: {
      method: 'PUT'
    }
  }
})

export {
  actions
}