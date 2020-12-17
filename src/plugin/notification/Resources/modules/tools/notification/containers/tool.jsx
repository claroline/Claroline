import {connect} from 'react-redux'

import {NotificationTool as NotificationToolComponent} from '#/plugin/notification/tools/notification/components/tool'
import {actions} from '#/plugin/notification/tools/notification/store'

const NotificationTool = connect(
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
)(NotificationToolComponent)

export {
  NotificationTool
}