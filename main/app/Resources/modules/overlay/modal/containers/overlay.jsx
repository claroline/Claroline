import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'

// the store to use
import {actions, reducer, selectors} from '#/main/app/overlay/modal/store'
// the component to connect
import {ModalOverlay as ModalOverlayComponent} from '#/main/app/overlay/modal/components/overlay'

const ModalOverlay = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      modals: selectors.modals(state)
    }),
    (dispatch) => ({
      fadeModal(modalId) {
        dispatch(actions.fadeModal(modalId))
      },
      hideModal(modalId) {
        dispatch(actions.hideModal(modalId))
      }
    })
  )(ModalOverlayComponent)
)

export {
  ModalOverlay
}
