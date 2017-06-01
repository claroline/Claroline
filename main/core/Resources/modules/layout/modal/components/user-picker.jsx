import Modal from 'react-bootstrap/lib/Modal'

import {t} from '#/main/core/translation'
import {BaseModal} from './base.jsx'
import React, {PropTypes as T} from 'react'
import {UserTypeahead} from '#/main/core/layout/typeahead/users/typeahead.jsx'

const UserPickerModal = props =>
  <BaseModal {...props}>
    <Modal.Body>
      <UserTypeahead {...props}/>
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

UserPickerModal.propTypes = {
  bsStyle: T.oneOf(['info', 'warning', 'success', 'danger']).isRequired,
  handleSelect: T.func.isRequired,
  handleRemove: T.func.isRequired,
  fadeModal: T.func.isRequired,
  selected: T.array.isRequired
}

UserPickerModal.defaultProps = {
  bsStyle: 'info',
  title: t('add_user'),
  selected: []
}

export {UserPickerModal}
