import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {Modal} from '#/main/app/overlays/modal/components/modal'

import {UserList} from '#/main/community/user/components/list'
import {User as UserTypes} from '#/main/community/prop-types'

import {selectors} from '#/main/community/modals/users/store'

const UsersModal = props => {
  const selectAction = props.selectAction(props.selected)

  return (
    <Modal
      icon="fa fa-fw fa-user"
      {...omit(props, 'url', 'selected', 'selectAction', 'reset')}
      className="data-picker-modal"
      bsSize="lg"
      onExited={props.reset}
    >
      <UserList
        name={selectors.STORE_NAME}
        url={props.url}
        primaryAction={undefined}
        actions={undefined}
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
  selected: T.arrayOf(T.shape(UserTypes.propTypes)).isRequired,
  reset: T.func.isRequired
}

UsersModal.defaultProps = {
  url: ['apiv2_user_list'],
  title: trans('users')
}

export {
  UsersModal
}
