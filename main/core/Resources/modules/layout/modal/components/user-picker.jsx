import React from 'react'
import {PropTypes as T} from 'prop-types'

import {t} from '#/main/core/translation'
import {Modal} from '#/main/app/overlay/modal/components/modal'
import {UserTypeahead} from '#/main/core/layout/typeahead/users/typeahead.jsx'
import {HelpBlock} from '#/main/core/layout/form/components/help-block.jsx'

/**
 *
 * @param props
 * @constructor
 * @deprecated only used in claco-form
 */
const UserPickerModal = props =>
  <Modal {...props}>
    <div className="modal-body">
      {props.help && <HelpBlock help={props.help} />}
      <UserTypeahead {...props}/>
    </div>
  </Modal>

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
