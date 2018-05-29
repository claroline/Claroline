import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/core/translation'
import {ConfirmModal} from '#/main/app/modals/confirm/components/confirm'

const MODAL_CONFIRM_REGISTRATION = 'MODAL_CONFIRM_REGISTRATION'

const ConfirmRegistrationModal = props =>
  <ConfirmModal
    {...props}
    icon="fa fa-fw fa-check"
    title={trans('user_registration')}
    question={trans('workspace_user_register_validation_message', {users: props.users.map(user => user.username).join(',')})}
    confirmButtonText={trans('register')}
    handleConfirm={() => props.register(props.users, props.workspace)}
  />

ConfirmRegistrationModal.propTypes = {
  register: T.func,
  users: T.array,
  workspace: T.object
}

export {
  MODAL_CONFIRM_REGISTRATION,
  ConfirmRegistrationModal
}
