import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'
import {ToolPage} from '#/main/core/tool'

import {selectors} from '#/main/authentication/administration/authentication/store'

const displayPasswordValidation = (data) => get(data, 'password._forceComplexity')
  || get(data, 'password.minLength')
  || get(data, 'password.requireLowercase')
  || get(data, 'password.requireUppercase')
  || get(data, 'password.requireNumber')
  || get(data, 'password.requireSpecialChar')

const AuthenticationParameters = (props) =>
  <ToolPage title={trans('parameters')}>
    <FormData
      name={selectors.FORM_NAME}
      target={['apiv2_authentication_parameters_update']}
      buttons={true}
      cancel={{
        type: LINK_BUTTON,
        target: props.path,
        exact: true
      }}
      definition={[
        {
          icon: 'fa fa-fw fa-sign-in',
          title: trans('login'),
          defaultOpened: true,
          fields: [
            {
              name: 'login.helpMessage',
              type: 'html',
              label: trans('message')
            }, {
              name: 'login.internalAccount',
              type: 'boolean',
              label: trans('display_internal_account', {}, 'security')
            }, {
              name: 'login.showClientIp',
              type: 'boolean',
              label: trans('display_client_ip', {}, 'security')
            }
          ]
        }, {
          icon: 'fa fa-fw fa-lock',
          title: trans('password'),
          fields: [
            {
              name: 'login.changePassword',
              type: 'boolean',
              label: trans('allow_change_password', {}, 'security')
            }, {
              name: 'password._forceComplexity',
              type: 'boolean',
              label: trans('force_password_complexity', {}, 'security'),
              calculated: displayPasswordValidation,
              onChange: (value) => {
                if (!value) {
                  props.update('password.minLength', 0)
                  props.update('password.requireLowercase', false)
                  props.update('password.requireUppercase', false)
                  props.update('password.requireNumber', false)
                  props.update('password.requireSpecialChar', false)
                }
              },
              linked: [
                {
                  name: 'password.minLength',
                  type: 'number',
                  label: trans('minLength', {}, 'security'),
                  displayed: displayPasswordValidation
                }, {
                  name: 'password.requireLowercase',
                  type: 'boolean',
                  label: trans('requireLowercase', {}, 'security'),
                  displayed: displayPasswordValidation
                }, {
                  name: 'password.requireUppercase',
                  type: 'boolean',
                  label: trans('requireUppercase', {}, 'security'),
                  displayed: displayPasswordValidation
                }, {
                  name: 'password.requireNumber',
                  type: 'boolean',
                  label: trans('requireNumber', {}, 'security'),
                  displayed: displayPasswordValidation
                }, {
                  name: 'password.requireSpecialChar',
                  type: 'boolean',
                  label: trans('requireSpecialChar', {}, 'security'),
                  displayed: displayPasswordValidation
                }
              ]
            }
          ]
        }
      ]}
    />
  </ToolPage>

AuthenticationParameters.propTypes = {
  path: T.string.isRequired,
  update: T.func.isRequired
}

export {
  AuthenticationParameters
}
