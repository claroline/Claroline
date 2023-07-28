import React from 'react'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'

import {selectors} from '#/main/authentication/administration/authentication/store/selectors'

const displayPasswordValidation = (data) => get(data, 'password._forceComplexity')
  || get(data, 'password.minLength')
  || get(data, 'password.requireLowercase')
  || get(data, 'password.requireUppercase')
  || get(data, 'password.requireNumber')
  || get(data, 'password.requireSpecialChar')

const AuthenticationTool = (props) => {
  return (
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
            }, {
              name: 'login.redirectAfterLoginOption',
              type: 'choice',
              label: trans('redirect_after_login_option'),
              options: {
                multiple: false,
                condensed: false,
                choices: {
                  'LAST': trans('last_page', {}, 'platform'),
                  'DESKTOP': trans('desktop', {}, 'platform'),
                  'URL': trans('url', {}, 'platform'),
                  'WORKSPACE_TAG': trans('workspace_tag', {}, 'platform')
                }
              }, linked: [
                {
                  name: 'login.redirectAfterLoginUrl',
                  type: 'string',
                  label: trans('url'),
                  displayed: (data) => 'URL' === get(data, 'login.redirectAfterLoginOption'),
                  required: true
                }, {
                  name: 'workspace.default_tag',
                  label: trans('tag', {}, 'tag'),
                  type: 'string',
                  displayed: (data) => 'WORKSPACE_TAG' === get(data, 'login.redirectAfterLoginOption'),
                  required: true
                }
              ]
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
  )
}

export {
  AuthenticationTool
}
