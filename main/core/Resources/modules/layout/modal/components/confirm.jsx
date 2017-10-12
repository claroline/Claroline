import React from 'react'
import {PropTypes as T} from 'prop-types'
import Modal from 'react-bootstrap/lib/Modal'
import classes from 'classnames'

import {t} from '#/main/core/translation'
import {BaseModal} from './base.jsx'

const ConfirmModal = props =>
  <BaseModal {...props}>
    <Modal.Body>
      {props.question}
    </Modal.Body>

    <button
      className={classes('modal-btn btn', props.dangerous ? 'btn-danger' : 'btn-primary')}
      onClick={() => {
        props.handleConfirm()
        props.fadeModal()
      }}
    >
      {props.confirmButtonText || t('confirm')}
    </button>
  </BaseModal>

ConfirmModal.propTypes = {
  confirmButtonText: T.string,
  dangerous: T.bool,
  question: T.string.isRequired,
  handleConfirm: T.func.isRequired,
  fadeModal: T.func.isRequired
}

export {
  ConfirmModal
}
