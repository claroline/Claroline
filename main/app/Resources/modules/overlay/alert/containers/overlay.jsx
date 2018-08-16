import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'

// the store to use
import {actions, reducer, selectors} from '#/main/app/overlay/alert/store'
// the component to connect
import {AlertOverlay as AlertOverlayComponent} from '#/main/app/overlay/alert/components/overlay'

const AlertOverlay = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      alerts: selectors.displayedAlerts(state)
    }),
    (dispatch) => ({
      removeAlert(type, message) {
        dispatch(actions.removeAlert(type, message))
      }
    })
  )(AlertOverlayComponent)
)

export {
  AlertOverlay
}
