import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'

import {NotificationsModal as NotificationsModalComponent} from '#/plugin/notification/modals/notifications/components/modal'
import {actions, reducer, selectors} from '#/plugin/notification/modals/notifications/store'

const NotificationsModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      loaded: selectors.loaded(state),
      results: selectors.results(state)
    }),
    (dispatch) => ({
      getNotifications() {
        dispatch(actions.getNotifications())
      }
    })
  )(NotificationsModalComponent)
)

export {
  NotificationsModal
}
