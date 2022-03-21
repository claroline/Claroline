import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {Modal} from '#/main/app/overlays/modal/components/modal'

import {UserList} from '#/main/core/user/components/list'
import {User as UserType} from '#/main/core/user/prop-types'

import {selectors} from '#/main/core/modals/users/store'

const UsersModal = props => {
  const selectAction = props.selectAction(props.selected)

  return (
    <Modal
      {...omit(props, 'url', 'selected', 'selectAction', 'reset')}
      icon="fa fa-fw fa-user"
      className="data-picker-modal"
      bsSize="lg"
      onExited={props.reset}
    >
      <UserList
        name={selectors.STORE_NAME}
        url={props.url}
        customDefinition={[
          {
            name: 'emails',
            label: trans('emails'),
            type: 'string',
            displayed: false,
            displayable: false,
            sortable: false
          }
        ]}
      />

      <Button
        label={trans('select', {}, 'actions')}
        {...selectAction}
        className="modal-btn btn"
        primary={true}
        disabled={0 === props.selected.length}
        onClick={props.fadeModal}
      />
    </Modal>
  )
}

UsersModal.propTypes = {
  url: T.oneOfType([T.string, T.array]),
  title: T.string,
  subtitle: T.string,
  selectAction: T.func.isRequired,
  fadeModal: T.func.isRequired,
  selected: T.arrayOf(T.shape(UserType.propTypes)).isRequired,
  reset: T.func.isRequired
}

UsersModal.defaultProps = {
  url: ['apiv2_user_list'],
  title: trans('users')
}

export {
  UsersModal
}
