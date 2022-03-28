import React, {Component, Fragment, createElement} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ContentHtml} from '#/main/app/content/components/html'

import {getSso} from '#/main/authentication/sso'
import {LoginAccount} from '#/main/app/security/login/components/account'

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
    const primarySso = this.props.sso.find(sso => sso.primary)
    const otherSso = this.props.sso.filter(sso => !sso.primary)

    // check if we want to show the form to log in with a claroline account
    const internalAccount = this.props.forceInternalAccount || this.props.internalAccount

    return (
      <Fragment>
        {this.props.help &&
          <div className={classes('login-container', {
            'login-with-sso': internalAccount && otherSso.length
          })}>
            <ContentHtml className="panel-body">{this.props.help}</ContentHtml>
          </div>
        }

        <div className={classes('login-container', {
          'login-with-sso': internalAccount && otherSso.length
        })}>
          {internalAccount &&
            <div className="authentication-column account-authentication-column">
              {primarySso && this.state.sso[primarySso.service] &&
                <div className="primary-external-authentication-column">
                  {createElement(this.state.sso[primarySso.service].components.button, Object.assign({}, primarySso, {
                    label: primarySso.label || trans('login_with_third_party_btn', {name: trans(primarySso.service, {}, 'oauth')})
                  }))}
                </div>
              }

              <p className="authentication-help">{trans('login_auth_claro_account', {platform: this.props.platformName})}</p>

              <LoginAccount
                username={this.props.username}
                resetPassword={this.props.resetPassword}
                login={this.props.login}
                onLogin={this.props.onLogin}
              />

              {0 !== otherSso.length &&
                <div className="authentication-or">
                  {trans('login_auth_or')}
                </div>
              }
            </div>
          }

          {0 !== otherSso.length &&
            <div className="authentication-column external-authentication-column">
              {!internalAccount && primarySso && this.state.sso[primarySso.service] &&
                <div className="primary-external-authentication-column">
                  {createElement(this.state.sso[primarySso.service].components.button, Object.assign({}, primarySso, {
                    label: primarySso.label || trans('login_with_third_party_btn', {name: trans(primarySso.service, {}, 'oauth')})
                  }))}
                </div>
              }

              <p className="authentication-help">{trans(!internalAccount ? 'login_auth_sso' : 'login_auth_sso_other')}</p>

              {otherSso.map(sso => this.state.sso[sso.service] ?
                createElement(this.state.sso[sso.service].components.button, Object.assign({}, sso, {
                  key: sso.service,
                  label: sso.label || trans('login_with_third_party_btn', {name: trans(sso.service, {}, 'oauth')})
                })) : null
              )}
            </div>
          }
        </div>

        {this.props.showClientIp &&
          <div className={classes('authentication-client-ip', {
            'login-with-sso': internalAccount && otherSso.length
          })}>{trans('location')} : {this.props.clientIp}</div>
        }

        {this.props.registration &&
          <Button
            className={classes('btn btn-lg btn-block btn-registration', {
              'login-with-sso': internalAccount && 0 !== otherSso.length
            })}
            type={LINK_BUTTON}
            label={trans('create-account', {}, 'actions')}
            target="/registration"
          />
        }
      </Fragment>
    )
  }
}

LoginMain.propTypes = {
  platformName: T.string.isRequired,
  help: T.string,
  internalAccount: T.bool.isRequired,
  forceInternalAccount: T.bool,
  showClientIp: T.bool.isRequired,
  clientIp: T.string,
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

LoginMain.defaultProps = {
  forceInternalAccount: false
}

export {
  LoginMain
}
