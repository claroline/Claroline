import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import Modal from 'react-bootstrap/lib/Modal'

import {trans} from '#/main/core/translation'
import {BaseModal} from './base.jsx'

const MessageModal = props =>
  <BaseModal
    {...props}
    icon={classes('fa fa-fw', {
      'fa-info-circle':          props.type === 'info',
      'fa-check-circle':         props.type === 'success',
      'fa-exclamation-triangle': props.type === 'warning',
      'fa-minus-circle':         props.type === 'danger'
    })}
  >
    <Modal.Body>
      {props.message}
    </Modal.Body>
  </BaseModal>

MessageModal.propTypes = {
  type: T.oneOf(['info', 'warning', 'success', 'danger']).isRequired,
  message: T.string.isRequired
}

MessageModal.defaultProps = {
  type: 'info'
}

export {
  MessageModal
}
