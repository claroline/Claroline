import React, {Component, Fragment, createElement} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'

import {getSso} from '#/main/authentication/sso'
import {selectors} from '#/main/app/security/login/store/selectors'

class LoginForm extends Component {
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

    return (
      <Fragment>
        <div className={classes('login-container', {
          'login-with-sso': otherSso.length
        })}>
          <div className="authentication-column account-authentication-column">
            {primarySso && this.state.sso[primarySso.service] &&
              <div className="primary-external-authentication-column">
                {createElement(this.state.sso[primarySso.service].components.button, {
                  service: primarySso.service,
                  label: primarySso.label || trans('login_with_third_party_btn', {name: trans(primarySso.service, {}, 'oauth')})
                })}
              </div>
            }

            <p className="authentication-help">{trans('login_auth_claro_account')}</p>

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
                      placeholder: trans('username_or_email'),
                      hideLabel: true,
                      type: 'username',
                      required: true
                    }, {
                      name: 'password',
                      label: trans('password'),
                      placeholder: trans('password'),
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
                label={trans('login')}
                callback={() => this.props.login(this.props.onLogin)}
                primary={true}
              />
            </FormData>

            <Button
              className="btn-link btn-block"
              type={LINK_BUTTON}
              label={trans('forgot_password')}
              target="/reset_password"
              primary={true}
            />

            {0 !== otherSso.length &&
              <div className="authentication-or">
                {trans('login_auth_or')}
              </div>
            }
          </div>

          {0 !== otherSso.length &&
            <div className="authentication-column external-authentication-column">
              <p className="authentication-help">{trans('login_auth_sso')}</p>

              {otherSso.map(sso => this.state.sso[sso.service] ?
                createElement(this.state.sso[sso.service].components.button, {
                  key: sso.service,
                  service: sso.service,
                  label: sso.label || trans('login_with_third_party_btn', {name: trans(sso.service, {}, 'oauth')})
                }) : null
              )}
            </div>
          }
        </div>

        {this.props.registration &&
          <Button
            className={classes('btn btn-lg btn-block btn-registration', {
              'login-with-sso': 0 !== otherSso.length
            })}
            type={LINK_BUTTON}
            label={trans('self-register', {}, 'actions')}
            target="/registration"
          />
        }
      </Fragment>
    )
  }
}


LoginForm.propTypes = {
  sso: T.arrayOf(T.shape({
    service: T.string.isRequired,
    label: T.string,
    primary: T.bool
  })).isRequired,
  registration: T.bool.isRequired,
  login: T.func.isRequired,
  onLogin: T.func
}

export {
  LoginForm
}
