import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import BaseModal from 'react-bootstrap/lib/Modal'

import {LoginForm} from '#/main/app/security/login/containers/form'

const LoginModal = props =>
  <BaseModal
    {...omit(props, 'fadeModal', 'hideModal', 'onLogin', 'onAbort')}
    autoFocus={true}
    enforceFocus={false}
    dialogClassName="login-modal"
    onHide={props.fadeModal}
    onExited={() => {
      if (props.onAbort) {
        props.onAbort()
      }

      props.hideModal()
    }}
  >
    <LoginForm
      onLogin={(response) => {
        if (props.onLogin) {
          props.onLogin(response)
        }

        props.fadeModal()
      }}
    />
  </BaseModal>

LoginModal.propTypes = {
  onLogin: T.func,
  onAbort: T.func,
  fadeModal: T.func.isRequired,
  hideModal: T.func.isRequired
}

export {
  LoginModal
}
