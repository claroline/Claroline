import {API_REQUEST} from '#/main/app/api'
import {makeActionCreator} from '#/main/app/store/actions'

const TRACKING_UPDATE = 'TRACKING_UPDATE'

const actions = {}

actions.updateTracking = makeActionCreator(TRACKING_UPDATE, 'tracking')

actions.commitData = (scoId, scoData) => ({
  [API_REQUEST]: {
    silent: true,
    url: ['apiv2_scormscotracking_update', {sco: scoId}],
    request: {
      body: JSON.stringify(scoData),
      method: 'PUT'
    },
    success: (data, dispatch) => {
      dispatch(actions.updateTracking(data))
    }
  }
})

export {
  actions,
  TRACKING_UPDATE
}