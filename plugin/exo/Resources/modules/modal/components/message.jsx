import React, {PropTypes as T} from 'react'
import classes from 'classnames'
import Alert from 'react-bootstrap/lib/Alert'
import Modal from 'react-bootstrap/lib/Modal'
import {t} from './../../utils/translate'
import {BaseModal} from './base.jsx'

export const MessageModal = props =>
  <BaseModal {...props}>
    <Modal.Body>
      <Alert bsStyle={props.bsStyle}>
        <span className={classes('fa',
          {'fa-info-circle': props.bsStyle === 'info'},
          {'fa-check-circle': props.bsStyle === 'success'},
          {'fa-warning': props.bsStyle === 'warning'},
          {'fa-warning': props.bsStyle === 'danger'}
        )}/>
        &nbsp;
        {props.message}
      </Alert>
    </Modal.Body>
    <Modal.Footer>
      <button
        className="btn btn-primary"
        onClick={() => props.fadeModal()}
      >
        {t('Ok')}
      </button>
    </Modal.Footer>
  </BaseModal>

MessageModal.propTypes = {
  bsStyle: T.oneOf(['info', 'warning', 'success', 'danger']).isRequired,
  message: T.string.isRequired,
  fadeModal: T.func.isRequired
}

MessageModal.defaultProps = {
  bsStyle: 'info'
}
