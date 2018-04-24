import {connect} from 'react-redux'
import {actions as modalActions} from '#/main/core/layout/modal/actions'

/**
 * HOC to give a component access to the modals methods
 * It works like `withRouter` from 'react-router', and injects :
 *
 * - showModal(modal)
 */
function withModal(Component) {
  const WithModal = connect(
    null,
    (dispatch) => ({
      showModal(modalType, modalProps) {
        dispatch(modalActions.showModal(modalType, modalProps))
      }
    })
  )(Component)

  WithModal.displayName = `WithModal(${Component.displayName})`

  return WithModal
}


export {
  withModal
}