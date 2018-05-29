import {connect} from 'react-redux'

// the store to use
import {actions, selectors} from '#/main/app/overlay/alert/store'

// the component to connect
import {AlertOverlay as AlertOverlayComponent} from '#/main/app/overlay/alert/components/overlay'

const AlertOverlay = connect(
  (state) => ({
    alerts: selectors.displayedAlerts(state)
  }),
  (dispatch) => ({
    removeAlert(type, message) {
      dispatch(actions.removeAlert(type, message))
    }
  })
)(AlertOverlayComponent)

export {
  AlertOverlay
}
