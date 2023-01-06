import {API_REQUEST} from '#/main/app/api'
import {actions as listActions} from '#/main/app/content/list/store/actions'

import {selectors} from '#/plugin/notification/account/notifications/store/selectors'

export const actions = {}

actions.delete = (notifications) => ({
  [API_REQUEST]: {
    url: ['apiv2_user_notifications_delete', {ids: notifications.map(n => n.id)}],
    request: {
      method: 'DELETE'
    },
    success: (response, dispatch) => dispatch(listActions.invalidateData(selectors.LIST_NAME))
  }
})

actions.markAsRead = (notifications) => ({
  [API_REQUEST]: {
    url: ['apiv2_user_notifications_read', {ids: notifications.map(n => n.id)}],
    request: {
      method: 'PUT'
    },
    success: (response, dispatch) => dispatch(listActions.invalidateData(selectors.LIST_NAME))
  }
})

actions.markAsUnread = (notifications) => ({
  [API_REQUEST]: {
    url: ['apiv2_user_notifications_unread', {ids: notifications.map(n => n.id)}],
    request: {
      method: 'PUT'
    },
    success: (response, dispatch) => dispatch(listActions.invalidateData(selectors.LIST_NAME))
  }
})
