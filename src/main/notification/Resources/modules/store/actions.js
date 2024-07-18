
import {API_REQUEST} from '#/main/app/api'
import {makeActionCreator} from '#/main/app/store/actions'

export const actions = {}

export const NOTIFICATIONS_LOAD = 'NOTIFICATIONS_LOAD'

actions.loadNotifications = makeActionCreator(NOTIFICATIONS_LOAD, 'notifications')
actions.fetchNotifications = () => (dispatch) => dispatch({
  [API_REQUEST]: {
    url: ['claro_notification_list'],
    success: (response) => dispatch(actions.loadNotifications(response))
  }
})
