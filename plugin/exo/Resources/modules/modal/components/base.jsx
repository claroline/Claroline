import React, {PropTypes as T} from 'react'
import Modal from 'react-bootstrap/lib/Modal'

export const BaseModal = props =>
  <Modal
    show={props.show}
    onHide={props.fadeModal}
    onExited={props.hideModal}
    dialogClassName={props.className}
  >
    <Modal.Header closeButton>
      <Modal.Title>{props.title}</Modal.Title>
    </Modal.Header>
    {props.children}
  </Modal>

BaseModal.propTypes = {
  fadeModal: T.func.isRequired,
  hideModal: T.func.isRequired,
  show: T.bool.isRequired,
  title: T.string.isRequired,
  className: T.string,
  children: T.node.isRequired
}

// required when testing proptypes on code instrumented by istanbul
// @see https://github.com/facebook/jest/issues/1824#issuecomment-250478026
BaseModal.displayName = 'BaseModal'
