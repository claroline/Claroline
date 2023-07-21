import {makeActionCreator} from '#/main/app/store/actions'
import {API_REQUEST, url} from '#/main/app/api'

import {constants} from '#/plugin/notification/modals/notifications/constants'

// actions
export const HEADER_NOTIFICATIONS_LOAD       = 'HEADER_NOTIFICATIONS_LOAD'
export const HEADER_NOTIFICATIONS_SET_LOADED = 'HEADER_NOTIFICATIONS_SET_LOADED'

// action creators
export const actions = {}

actions.load = makeActionCreator(HEADER_NOTIFICATIONS_LOAD, 'results')
actions.setLoaded = makeActionCreator(HEADER_NOTIFICATIONS_SET_LOADED, 'loaded')

actions.getNotifications = () => (dispatch) => dispatch({
  [API_REQUEST]: {
    silent: true,
    url: url(['apiv2_user_notifications_list'], {
      page: 0,
      limit: constants.LIMIT_RESULTS,
      filters: {
        read: false,
        removed: false,
        sent: false
      },
      sortBy: '-meta.date'
    }),
    before: () => dispatch(actions.setLoaded(false)),
    success: (response) => dispatch(actions.load(response.data || []))
  }
})
