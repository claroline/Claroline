import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {t} from '#/main/core/translation'
import {Modal} from '#/main/app/overlay/modal/components/modal'
import {UserTypeahead} from '#/main/core/layout/typeahead/users/typeahead'
import {ContentHelp} from '#/main/app/content/components/help'

/**
 *
 * @param props
 * @constructor
 * @deprecated only used in claco-form
 */
const UserPickerModal = props =>
  <Modal
    {...omit(props, 'handleRemove', 'handleSelect', 'help', 'unique')}
  >
    <div className="modal-body">
      {props.help &&
        <ContentHelp help={props.help} />
      }

      <UserTypeahead {...props}/>
    </div>
  </Modal>

UserPickerModal.propTypes = {
  bsStyle: T.oneOf(['info', 'warning', 'success', 'danger']).isRequired,
  handleSelect: T.func.isRequired,
  handleRemove: T.func.isRequired,
  fadeModal: T.func.isRequired,
  selected: T.array.isRequired,
  help: T.string,
  unique: T.bool
}

UserPickerModal.defaultProps = {
  bsStyle: 'info',
  title: t('add_user'),
  selected: [],
  unique: false
}

export {
  UserPickerModal
}
