import {API_REQUEST, url} from '#/main/app/api'
import {makeActionCreator} from '#/main/app/store/actions'
import {actions as listActions} from '#/main/app/content/list/store/actions'

import {selectors} from '#/integration/big-blue-button/integration/bbb/store'

export const MEETINGS_UPDATE = 'MEETINGS_UPDATE'
export const MEETINGS_SET_LOADED = 'MEETINGS_SET_LOADED'

const actions = {}

actions.setLoaded = makeActionCreator(MEETINGS_SET_LOADED, 'loaded')
actions.updateMeetings = makeActionCreator(MEETINGS_UPDATE, 'maxMeetings', 'maxMeetingParticipants', 'maxParticipants', 'activeMeetingsCount', 'participantsCount', 'servers', 'allowRecords')

actions.endMeetings = (ids) => (dispatch) => dispatch({
  [API_REQUEST]: {
    url: url(['apiv2_bbb_integration_meetings_end'], {ids: ids}),
    request: {
      method: 'PUT'
    },
    success: () => {
      dispatch(actions.fetchInfo())
      dispatch(listActions.invalidateData(selectors.STORE_NAME+'.meetings'))
    }
  }
})

actions.fetchInfo = () => (dispatch) => dispatch({
  [API_REQUEST]: {
    url: ['apiv2_bbb_integration_info'],
    silent: true,
    request: {
      method: 'GET'
    },
    before: () => dispatch(actions.setLoaded(false)),
    success: (data) => dispatch(actions.updateMeetings(data.maxMeetings, data.maxMeetingParticipants, data.maxParticipants, data.activeMeetingsCount, data.participantsCount, data.servers, data.allowRecords))
  }
})

actions.syncRecordings = () => (dispatch) => dispatch({
  [API_REQUEST]: {
    url: url(['apiv2_bbb_integration_recordings_sync']),
    request: {
      method: 'POST'
    },
    success: () => dispatch(listActions.invalidateData(selectors.STORE_NAME+'.recordings'))
  }
})

export {
  actions
}
