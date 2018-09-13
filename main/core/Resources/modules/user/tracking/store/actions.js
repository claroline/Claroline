import {makeActionCreator} from '#/main/app/store/actions'
import {API_REQUEST, url} from '#/main/app/api'

import {currentUser} from '#/main/core/user/current'

const authenticatedUser = currentUser()

const TRACKINGS_INIT = 'TRACKINGS_INIT'

const actions = {}

actions.initTrackings = makeActionCreator(TRACKINGS_INIT, 'trackings')

actions.loadTrackings = (startDate, endDate) => ({
  [API_REQUEST]: {
    url: url(['apiv2_user_trackings_list', {user: authenticatedUser.id}], {startDate: startDate, endDate: endDate}),
    request: {
      method: 'GET'
    },
    success: (response, dispatch) => {
      dispatch(actions.initTrackings(response.data))
    }
  }
})

export {
  actions,
  TRACKINGS_INIT
}