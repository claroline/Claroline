import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import {Portal} from 'react-overlays'

import {Modal as ModalTypes} from '#/main/app/overlays/modal/prop-types'

// get all modals registered in the application
import {registry} from '#/main/app/modals/registry'

/**
 * Renders the current displayed modal if any.
 */
class ModalOverlay extends Component {
  render() {
    return (
      <Portal container={() => document.querySelector('.app-modal-container')}>
        <div className="app-modal" ref={(el) => this.container = el}>
          {this.props.modals.map((modal, index) => React.createElement(
            // grab the correct modal component from registry
            registry.get(modal.type),

            // constructs modal props
            Object.assign({
              key: modal.id,
              show: !modal.fading,
              disabled: 0 !== index && !modal.fading,
              container: this.container,
              fadeModal: () => this.props.fadeModal(modal.id),
              hideModal: () => this.props.hideModal(modal.id)
            }, modal.props || {})
          ))}
        </div>
      </Portal>
    )
  }
}

ModalOverlay.propTypes = {
  container: T.oneOfType([T.node, T.element]),
  show: T.bool.isRequired,
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
