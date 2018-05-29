import {connect} from 'react-redux'

// the store to use
import {actions, selectors} from '#/main/app/overlay/modal/store'

// the component to connect
import {ModalOverlay as ModalOverlayComponent} from '#/main/app/overlay/modal/components/overlay'

const ModalOverlay = connect(
  (state) => ({
    modal: selectors.modal(state)
  }),
  (dispatch) => ({
    fadeModal() {
      dispatch(actions.fadeModal())
    },
    hideModal() {
      dispatch(actions.hideModal())
    }
  })
)(ModalOverlayComponent)

export {
  ModalOverlay
}
