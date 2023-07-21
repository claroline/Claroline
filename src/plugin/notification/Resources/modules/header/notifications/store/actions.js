import {makeActionCreator} from '#/main/app/store/actions'
import {API_REQUEST} from '#/main/app/api'

export const NOTIFICATIONS_COUNT = 'NOTIFICATIONS_COUNT'

// action creators
export const actions = {}

actions.setCount = makeActionCreator(NOTIFICATIONS_COUNT, 'count')

actions.countNotifications = () => (dispatch) => dispatch({
  [API_REQUEST]: {
    silent: true,
    url: ['apiv2_user_notifications_count'],
    success: (response) => dispatch(actions.setCount(response))
  }
})
