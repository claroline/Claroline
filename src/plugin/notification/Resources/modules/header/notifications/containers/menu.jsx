import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'
import {selectors as configSelectors} from '#/main/app/config/store'
import {selectors as securitySelectors} from '#/main/app/security/store'

import {NotificationsMenu as NotificationsMenuComponent} from '#/plugin/notification/header/notifications/components/menu'
import {actions, reducer, selectors} from '#/plugin/notification/header/notifications/store'

const NotificationsMenu = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      isAuthenticated: securitySelectors.isAuthenticated(state),
      refreshDelay: configSelectors.param(state, 'notifications.refreshDelay'),
      count: selectors.count(state)
    }),
    (dispatch) => ({
      countNotifications() {
        return dispatch(actions.countNotifications())
      }
    })
  )(NotificationsMenuComponent)
)

export {
  NotificationsMenu
}
