import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import {selectors} from '#/main/app/security/login/store'
import {trans} from '#/main/app/intl'
import {Button} from '#/main/app/action'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'

/**
 * Form to log in with a Claroline account.
 */
class LoginAccount extends Component {
  constructor(props) {
    super(props)

    this.state = {
      inProgress: false
    }
  }

  render() {
    return (
      <FormData
        name={selectors.FORM_NAME}
        alertExit={false}
        sections={[
          {
            title: trans('general'),
            primary: true,
            fields: [
              {
                name: 'username',
                label: trans('username_or_email'),
                placeholder: this.props.username ? trans('username_or_email') : trans('email'),
                hideLabel: true,
                type: 'username',
                required: true
              }, {
                name: 'password',
                label: trans('password'),
                placeholder: trans('password'),
                autoComplete: 'current-password',
                hideLabel: true,
                type: 'password',
                required: true
              }
            ]
          }
        ]}
      >
        <Button
          className="btn btn-block btn-emphasis"
          type={CALLBACK_BUTTON}
          htmlType="submit"
          label={!this.state.inProgress ? trans('login'):trans('login_in_progress')}
          disabled={this.state.inProgress}
          callback={() => {
            this.setState({inProgress: true})
            this.props.login(this.props.onLogin).then(() => this.setState({inProgress: false}))
          }}
          primary={true}
        />

        {this.props.resetPassword &&
          <Button
            className="btn-link btn-block"
            type={LINK_BUTTON}
            label={trans('forgot_password')}
            target="/reset_password"
            primary={true}
          />
        }
      </FormData>
    )
  }
}


LoginAccount.propTypes = {
  username: T.bool,
  resetPassword: T.bool,

  login: T.func.isRequired,
  onLogin: T.func.isRequired
}

export {
  LoginAccount
}
