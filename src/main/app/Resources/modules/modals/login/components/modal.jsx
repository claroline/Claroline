import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import BaseModal from 'react-bootstrap/lib/Modal'

import {LoginMain} from '#/main/app/security/login/containers/main'

class LoginModal extends Component {
  constructor(props) {
    super(props)

    this.state = {
      aborted: true
    }
  }

  render() {
    return (
      <BaseModal
        {...omit(this.props, 'fadeModal', 'hideModal', 'onLogin', 'onAbort')}
        autoFocus={true}
        enforceFocus={false}
        dialogClassName="login-modal"
        bsSize="lg"
        onHide={this.props.fadeModal}
        onExited={() => {
          if (this.props.onAbort && this.state.aborted) {
            this.props.onAbort()
          }

          this.props.hideModal()
        }}
      >
        <LoginMain
          onLogin={(response) => {
            this.setState({
              aborted: false
            }, () => {
              if (this.props.onLogin) {
                this.props.onLogin(response)
              }

              this.props.fadeModal()
            })
          }}
        />
      </BaseModal>
    )
  }
}


LoginModal.propTypes = {
  onLogin: T.func,
  onAbort: T.func,
  fadeModal: T.func.isRequired,
  hideModal: T.func.isRequired
}

export {
  LoginModal
}
