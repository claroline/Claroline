import React, {PropTypes as T} from 'react'
import Modal from 'react-bootstrap/lib/Modal'
import classes from 'classnames'
import {t} from './../../utils/translate'
import {BaseModal} from './base.jsx'

export const ConfirmModal = props =>
  <BaseModal {...props}>
    <Modal.Body>
      {props.question}
    </Modal.Body>
    <Modal.Footer>
      <button
        className="btn btn-default"
        onClick={props.fadeModal}
      >
        {t('cancel')}
      </button>
      <button
        className={classes('btn', props.isDangerous ? 'btn-danger' : 'btn-primary')}
        onClick={() => {
          props.handleConfirm()
          props.fadeModal()
        }}
      >
        {props.confirmButtonText || t('Ok')}
      </button>
    </Modal.Footer>
  </BaseModal>

ConfirmModal.propTypes = {
  confirmButtonText: T.string,
  isDangerous: T.bool,
  question: T.string.isRequired,
  handleConfirm: T.func.isRequired,
  fadeModal: T.func.isRequired
}
