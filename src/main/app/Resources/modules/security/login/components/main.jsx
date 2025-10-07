import React, {Component, createElement} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans, transChoice} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {LINK_BUTTON} from '#/main/app/buttons'

import {getSso} from '#/main/authentication/sso'
import {LoginAccount} from '#/main/app/security/login/components/account'
import {Divider} from '#/main/app/components/divider'

class LoginMain extends Component {
  constructor(props) {
    super(props)

    this.state = {
      sso: {}
    }
  }

  componentDidMount() {
    if (0 !== this.props.sso.length) {
      Promise.all(
        this.props.sso.map(sso => getSso(sso.service))
      ).then(
        // we convert the list into an object keyed with service name for easier access in render
        all => this.setState({sso: all.reduce((acc, current) => Object.assign(acc, {[current.default.name]: current.default}), {})})
      )
    }
  }

  render() {
    // check if we want to show the form to log in with a claroline account
    const internalAccount = this.props.forceInternalAccount || this.props.internalAccount

    const displayedSso = (this.props.sso || []).filter(sso => undefined === sso.displayed || sso.displayed)

    return (
      <>
        {internalAccount &&
          <LoginAccount
            username={this.props.username}
            resetPassword={this.props.resetPassword}
            login={this.props.login}
            onLogin={this.props.onLogin}
          />
        }

        {this.props.registration &&
          <Button
            className="btn btn-body mt-1 w-100"
            type={LINK_BUTTON}
            label={trans('create-account', {}, 'actions')}
            target="/registration"
          />
        }

        {internalAccount && 0 !== displayedSso.length &&
          <Divider
            className="my-5"
            label={trans('login_auth_or')}
            align="center"
          />
        }

        {0 !== displayedSso.length &&
          <>
            <p className="lead text-center text-body-secondary mb-5 visually-hidden">
              {transChoice(!internalAccount ? 'login_auth_sso' : 'login_auth_sso_other', displayedSso.length)}
            </p>

            <div role="presentation" className="d-grid gap-1">
              {displayedSso.map(sso => this.state.sso[sso.service] ?
                createElement(this.state.sso[sso.service].components.button, Object.assign({}, sso, {
                  key: sso.service,
                  label: sso.label || trans('login_with_third_party_btn', {name: trans(sso.service, {}, 'oauth')}),
                  primary: 1 === displayedSso.length
                })) : null
              )}
            </div>
          </>
        }
      </>
    )
  }
}

LoginMain.propTypes = {
  platformName: T.string.isRequired,
  internalAccount: T.bool.isRequired,
  forceInternalAccount: T.bool,
  sso: T.arrayOf(T.shape({
    service: T.string.isRequired,
    label: T.string,
    primary: T.bool
  })).isRequired,
  username: T.bool.isRequired,
  registration: T.bool.isRequired,
  resetPassword: T.bool.isRequired,
  login: T.func.isRequired,
  onLogin: T.func
}

export {
  LoginMain
}
