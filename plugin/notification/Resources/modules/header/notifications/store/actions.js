import {makeActionCreator} from '#/main/app/store/actions'
import {API_REQUEST, url} from '#/main/app/api'

import {constants} from '#/plugin/notification/header/notifications/constants'

// actions
export const NOTIFICATIONS_LOAD       = 'NOTIFICATIONS_LOAD'
export const NOTIFICATIONS_SET_LOADED = 'NOTIFICATIONS_SET_LOADED'
export const NOTIFICATIONS_COUNT      = 'NOTIFICATIONS_COUNT'

// action creators
export const actions = {}

actions.setCount = makeActionCreator(NOTIFICATIONS_COUNT, 'count')
actions.load = makeActionCreator(NOTIFICATIONS_LOAD, 'results')
actions.setLoaded = makeActionCreator(NOTIFICATIONS_SET_LOADED, 'loaded')

actions.getNotifications = () => ({
  [API_REQUEST]: {
    silent: true,
    url: url(['apiv2_user_notifications_list'], {
      page: 0,
      limit: constants.LIMIT_RESULTS,
      filters: {read: false},
      sortBy: '-notification.meta.created'
    }),
    before: (dispatch) => dispatch(actions.setLoaded(false)),
    success: (response, dispatch) => {
      dispatch(actions.load(response.data || []))
      dispatch(actions.setCount(response.totalResults))
    }
  }
})

actions.countNotifications = () => ({
  [API_REQUEST]: {
    silent: true,
    url: ['apiv2_user_notifications_count'],
    success: (response, dispatch) => dispatch(actions.setCount(response))
  }
})
