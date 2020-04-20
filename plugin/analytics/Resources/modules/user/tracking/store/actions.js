import {makeActionCreator} from '#/main/app/store/actions'
import {API_REQUEST, url} from '#/main/app/api'

export const TRACKING_INIT = 'TRACKING_INIT'

export const actions = {}

actions.initTrackings = makeActionCreator(TRACKING_INIT, 'tracking')

actions.loadTracking = (userId, startDate = null, endDate = null) => (dispatch) => {
  const params = {}
  if (startDate) {
    params.startDate = startDate
  }
  if (endDate) {
    params.endDate = endDate
  }

  return dispatch({
    [API_REQUEST]: {
      url: url(['apiv2_user_tracking_list', {user: userId}], params),
      request: {
        method: 'GET'
      },
      success: (response) => dispatch(actions.initTrackings(response.data))
    }
  })
}
