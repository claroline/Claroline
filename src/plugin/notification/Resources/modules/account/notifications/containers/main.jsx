import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'

import {NotificationMain as NotificationMainComponent} from '#/plugin/notification/account/notifications/components/main'
import {actions, reducer, selectors} from '#/plugin/notification/account/notifications/store'

const NotificationMain = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    null,
    (dispatch) => ({
      markAsRead(notifications) {
        dispatch(actions.markAsRead(notifications))
      },
      markAsUnread(notifications) {
        dispatch(actions.markAsUnread(notifications))
      },
      delete(notifications) {
        dispatch(actions.delete(notifications))
      }
    })
  )(NotificationMainComponent)
)

export {
  NotificationMain
}
