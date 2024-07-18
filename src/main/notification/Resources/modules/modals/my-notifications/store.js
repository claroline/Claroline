

import {API_REQUEST} from '#/main/app/api'
import {makeReducer} from '#/main/app/store/reducer'
import {makeActionCreator} from '#/main/app/store/actions'


const STORE_NAME = 'myNotifications'

const notifications = (state) => state[STORE_NAME]

const selectors = {
  STORE_NAME,
  notifications
}

const actions = {}

const NOTIFICATIONS_LOAD = 'NOTIFICATIONS_LOAD'

actions.loadNotifications = makeActionCreator(NOTIFICATIONS_LOAD, 'notifications')
actions.fetchNotifications = () => (dispatch) => dispatch({
  [API_REQUEST]: {
    url: ['claro_notification_list'],
    success: (response) => dispatch(actions.loadNotifications(response))
  }
})

const reducer = makeReducer([], {
  [NOTIFICATIONS_LOAD]: (state, action) => action.notifications || state
})

export {
  actions,
  reducer,
  selectors
}