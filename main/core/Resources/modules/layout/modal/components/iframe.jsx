import React from 'react'
import {PropTypes as T} from 'prop-types'
import Modal from 'react-bootstrap/lib/Modal'

import {t} from '#/main/core/translation'
import {BaseModal} from './base.jsx'

const IframeModal = props =>
  <BaseModal {...props}>
    <Modal.Body>
      <iframe
        width={props.width}
        height={props.height}
        src={props.src} />
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

IframeModal.propTypes = {
  src: T.string.isRequired,
  width: T.number.isRequired,
  height: T.number.isRequired,
  fadeModal: T.func.isRequired
}

export {IframeModal}