import React from 'react'
import {PropTypes as T} from 'prop-types'
import Modal from 'react-bootstrap/lib/Modal'

import {t} from '#/main/core/translation'
import {BaseModal} from './base.jsx'
import {UserTypeahead} from '#/main/core/layout/typeahead/users/typeahead.jsx'
import {HelpBlock} from '#/main/core/layout/form/components/help-block.jsx'

const UserPickerModal = props =>
  <BaseModal {...props}>
    <Modal.Body>
      {props.help && <HelpBlock help={props.help} />}
      <UserTypeahead {...props}/>
    </Modal.Body>
  </BaseModal>

UserPickerModal.propTypes = {
  bsStyle: T.oneOf(['info', 'warning', 'success', 'danger']).isRequired,
  handleSelect: T.func.isRequired,
  handleRemove: T.func.isRequired,
  fadeModal: T.func.isRequired,
  selected: T.array.isRequired,
  help: T.string
}

UserPickerModal.defaultProps = {
  bsStyle: 'info',
  title: t('add_user'),
  selected: []
}

export {
  UserPickerModal
}
