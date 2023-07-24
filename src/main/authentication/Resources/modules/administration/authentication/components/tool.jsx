import React from 'react'

import {LINK_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl/translation'
import {FormData} from '#/main/app/content/form/containers/data'
import {selectors} from '#/main/authentication/administration/authentication/store/selectors'
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
          title: trans('general'),
          primary: true
        }, {
          icon: 'fa fa-fw fa-lock',
          title: trans('passwordValidate', {}, 'security'),
          fields: [
            {
              name: 'password.minLength',
              type: 'number',
              label: trans('minLength', {}, 'security')
            },
            {
              name: 'password.requireLowercase',
              type: 'boolean',
              label: trans('requireLowercase', {}, 'security')
            },
            {
              name: 'password.requireUppercase',
              type: 'boolean',
              label: trans('requireUppercase', {}, 'security')
            },
            {
              name: 'password.requireNumber',
              type: 'boolean',
              label: trans('requireNumber', {}, 'security')
            },
            {
              name: 'password.requireSpecialChar',
              type: 'boolean',
              label: trans('requireSpecialChar', {}, 'security')
            }
          ]
        }, {
          icon: 'fa fa-fw fa-sign-in',
          title: trans('login'),
          fields: [
            {
              name: 'login.helpMessage',
              type: 'html',
              label: trans('message')
            }, {
              name: 'login.redirectAfterLoginOption',
              type: 'choice',
              label: trans('redirect_after_login_option'),
              options: {
                multiple: false,
                condensed: false,
                choices: {
                  'DESKTOP': trans('desktop', {}, 'platform'),
                  'URL': trans('url', {}, 'platform'),
                  'WORKSPACE_TAG': trans('workspace_tag', {}, 'platform'),
                  'LAST': trans('last_page', {}, 'platform')
                }
              }, linked: [{
                name: 'login.redirectAfterLoginUrl',
                type: 'string',
                label: trans('redirect_after_login_url'),
                displayed: (data) => data.login.redirectAfterLoginOption === 'URL',
                hideLabel: true
              }, {
                name: 'workspace.default_tag',
                label: trans('default_workspace_tag'),
                type: 'string',
                displayed: (data) => data.login.redirectAfterLoginOption === 'WORKSPACE_TAG',
                hideLabel: true
              }]
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
