import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON, LINK_BUTTON, URL_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'

import {selectors} from '#/main/app/security/login/store/selectors'

const LoginForm = props =>
  <Fragment>
    <div className={classes('login-container', {
      'login-with-sso': 0 !== props.sso.length
    })}>
      <div className="authentication-column account-authentication-column">
        {props.primarySso &&
          <div className="primary-external-authentication-column">
            PLACEHOLDER FOR PRIMARY SSO
          </div>
        }

        <p className="authentication-help">{trans('login_auth_claro_account') }</p>

        <FormData
          name={selectors.FORM_NAME}
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
                }, {
                  name: 'remember_me',
                  label: trans('keep_me_logged_in'),
                  type: 'boolean'
                }
              ]
            }
          ]}
        >
          <Button
            className="btn btn-block btn-emphasis"
            type={CALLBACK_BUTTON}
            label={trans('login')}
            callback={() => props.login(props.onLogin)}
            primary={true}
          />
        </FormData>

        <Button
          className="btn-link btn-block"
          type={URL_BUTTON}
          label={trans('forgot_password')}
          target="/reset_password"
          primary={true}
        />

        {0 !== props.sso.length &&
          <div className="authentication-or">
            {trans('login_auth_or')}
          </div>
        }
      </div>

      {0 !== props.sso.length &&
        <div className="authentication-column external-authentication-column">
          <p className="authentication-help">{trans('login_auth_sso')}</p>

          PLACEHOLDER FOR ALL ENABLED SSO
        </div>
      }
    </div>

    {props.registration &&
      <Button
        className={classes('btn btn-lg btn-block btn-registration', {
          'login-with-sso': 0 !== props.sso.length
        })}
        type={LINK_BUTTON}
        label={trans('self-register', {}, 'actions')}
        target="/registration"
      />
    }
  </Fragment>

LoginForm.propTypes = {
  primarySso: T.shape({
    // TODO : prop-types
  }),
  sso: T.arrayOf(T.shape({
    // TODO : prop-types
  })).isRequired,
  registration: T.bool.isRequired,
  login: T.func.isRequired,
  onLogin: T.func
}

export {
  LoginForm
}
