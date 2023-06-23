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
          primary: true,
        }, {
          icon: 'fa fa-fw fa-lock',
          title: trans('passwordValidate', {}, 'security'),
          fields: [
            {
              name: 'minLength',
              type: 'number',
              label: trans('minLength', {}, 'security')
            },
            {
              name: 'requireLowercase',
              type: 'boolean',
              label: trans('requireLowercase', {}, 'security')
            },
            {
              name: 'requireUppercase',
              type: 'boolean',
              label: trans('requireUppercase', {}, 'security')
            },
            {
              name: 'requireNumber',
              type: 'boolean',
              label: trans('requireNumber', {}, 'security')
            },
            {
              name: 'requireSpecialChar',
              type: 'boolean',
              label: trans('requireSpecialChar', {}, 'security')
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
