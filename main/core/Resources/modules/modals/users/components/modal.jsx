import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {Button} from '#/main/app/action/components/button'
import {Modal} from '#/main/app/overlay/modal/components/modal'
import {ListData} from '#/main/app/content/list/containers/data'

import {trans} from '#/main/core/translation'
import {selectors} from '#/main/core/modals/users/store'
import {UserList} from '#/main/core/administration/user/user/components/user-list'
import {User as UserType} from '#/main/core/user/prop-types'

const UsersPickerModal = props => {
  const selectAction = props.selectAction(props.selected)

  //apiv2_user_list_registerable = filter by current user organizations
  return (
    <Modal
      {...omit(props, 'confirmText', 'selected', 'selectAction', 'resetSelect')}
      className="users-picker-modal"
      icon="fa fa-fw fa-user"
      bsSize="lg"
      onExiting={() => props.resetSelect()}
    >
      <ListData
        name={selectors.STORE_NAME}
        fetch={{
          url: ['apiv2_user_list_registerable'],
          autoload: true
        }}
        definition={[
          {
            name: 'username',
            type: 'username',
            label: trans('username'),
            displayed: true
          }, {
            name: 'lastName',
            type: 'string',
            label: trans('last_name'),
            displayed: true
          }, {
            name: 'firstName',
            type: 'string',
            label: trans('first_name'),
            displayed: true
          }, {
            name: 'email',
            type: 'email',
            label: trans('email'),
            displayed: true
          }
        ]}
        card={UserList.card}
        display={props.display}
      />

      <Button
        label={props.confirmText}
        {...selectAction}
        className="modal-btn btn"
        primary={true}
        disabled={0 === props.selected.length}
        onClick={props.fadeModal}
      />
    </Modal>
  )
}

UsersPickerModal.propTypes = {
  title: T.string,
  confirmText: T.string,
  selectAction: T.func.isRequired,
  fadeModal: T.func.isRequired,
  selected: T.arrayOf(T.shape(UserType.propTypes)).isRequired,
  resetSelect: T.func.isRequired
}

UsersPickerModal.defaultProps = {
  title: trans('user_selector'),
  confirmText: trans('select', {}, 'actions')
}

export {
  UsersPickerModal
}
