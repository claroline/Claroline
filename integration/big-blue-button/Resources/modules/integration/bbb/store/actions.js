import {API_REQUEST} from '#/main/app/api'
import {makeActionCreator} from '#/main/app/store/actions'

export const MEETINGS_UPDATE = 'MEETINGS_UPDATE'
export const MEETINGS_SET_LOADED = 'MEETINGS_SET_LOADED'

const actions = {}

actions.setLoaded = makeActionCreator(MEETINGS_SET_LOADED, 'loaded')
actions.updateMeetings = makeActionCreator(MEETINGS_UPDATE, 'maxMeetings', 'maxMeetingParticipants', 'maxParticipants', 'activeMeetingsCount', 'participantsCount', 'meetings')

actions.endMeeting = (meetingId) => (dispatch) => dispatch({
  [API_REQUEST]: {
    url: ['apiv2_bbb_meeting_end', {id: meetingId}],
    request: {
      method: 'PUT'
    },
    success: () => dispatch(actions.fetchMeetings())
  }
})

actions.fetchMeetings = () => (dispatch) => dispatch({
  [API_REQUEST]: {
    url: ['apiv2_bbb_moderator_meetings_list'],
    request: {
      method: 'GET'
    },
    before: () => dispatch(actions.setLoaded(false)),
    success: (data) => dispatch(actions.updateMeetings(data.maxMeetings, data.maxMeetingParticipants, data.maxParticipants, data.activeMeetingsCount, data.participantsCount, data.meetings))
  }
})

export {
  actions
}
