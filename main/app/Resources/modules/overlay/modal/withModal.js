import {connect} from 'react-redux'

import {actions} from '#/main/app/overlay/modal/store'

/**
 * HOC to give a component access to the modals methods.
 * It works like `withRouter` from 'react-router', and injects :
 *
 * - showModal(modal)
 */
function withModal(Component) {
  const WithModal = connect(
    null,
    (dispatch) => ({
      showModal(modalType, modalProps) {
        dispatch(actions.showModal(modalType, modalProps))
      }
    })
  )(Component)

  WithModal.displayName = `WithModal(${Component.displayName})`

  return WithModal
}


export {
  withModal
}