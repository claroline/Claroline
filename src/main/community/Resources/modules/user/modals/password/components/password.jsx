import React, {useState} from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {DataInput} from '#/main/app/data/components/input'

import {User as UserTypes} from '#/main/community/prop-types'

const PasswordModal = props => {
  const [password, setPassword] = useState(null)

  return (
    <Modal
      {...omit(props, 'user', 'changePassword')}
      icon="fa fa-fw fa-lock"
      title={trans('change_password')}
      subtitle={props.user.username}
    >
      <div className="modal-body">
        <DataInput
          id="userPassword"
          type="password"
          label={trans('password')}
          value={password}
          onChange={setPassword}
          required={true}
        />
      </div>

      <Button
        className="modal-btn"
        variant="btn"
        size="lg"
        type={CALLBACK_BUTTON}
        primary={true}
        label={trans('create', {}, 'actions')}
        disabled={isEmpty(password)}
        callback={() => {
          props.changePassword(props.user, password)
          props.fadeModal()
        }}
      />
    </Modal>
  )
}

PasswordModal.propTypes = {
  user: T.shape(UserTypes.propTypes),
  changePassword: T.func.isRequired,
  fadeModal: T.func.isRequired
}

export {
  PasswordModal
}
