import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Modal as ModalTypes} from '#/main/app/overlay/modal/prop-types'

// get all modals registered in the application
import {registry} from '#/main/app/modals/registry'

/**
 * Renders the current displayed modal if any.
 */
const ModalOverlay = props =>
  <div className="modal-overlay">
    {props.modals.map((modal, index) => React.createElement(
      // grab the correct modal component from registry
      registry.get(modal.type),

      // constructs modal props
      Object.assign({
        key: modal.id,
        show: !modal.fading,
        disabled: 0 !== index,
        fadeModal: () => props.fadeModal(modal.id),
        hideModal: () => props.hideModal(modal.id)
      }, modal.props || {})
    ))}
  </div>

ModalOverlay.propTypes = {
  modal: T.shape(ModalTypes.propTypes),
  modals: T.arrayOf(T.shape(
    ModalTypes.propTypes
  )),
  fadeModal: T.func.isRequired,
  hideModal: T.func.isRequired
}

export {
  ModalOverlay
}
