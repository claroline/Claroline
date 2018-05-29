import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {Modal as ModalTypes} from '#/main/app/overlay/modal/prop-types'

// get all modals registered in the application
import {registry} from '#/main/app/modals/registry'

/**
 * Renders the current displayed modal if any.
 *
 * @param props
 * @constructor
 */
const ModalOverlay = props => get(props, 'modal.type') && React.createElement(
  // grab the correct modal component from registry
  registry.get(props.modal.type),
  // constructs modal props
  Object.assign({
    show: !props.modal.fading,
    fadeModal: props.fadeModal,
    hideModal:() => props.hideModal
  }, props.modal.props || {})
)

ModalOverlay.propTypes = {
  modal: T.shape(
    ModalTypes.propTypes
  ),
  fadeModal: T.func.isRequired,
  hideModal: T.func.isRequired
}

export {
  ModalOverlay
}
