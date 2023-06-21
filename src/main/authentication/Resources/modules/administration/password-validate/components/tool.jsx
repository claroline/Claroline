import React from 'react'

import {LINK_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl/translation'
import {FormData} from '#/main/app/content/form/containers/data'
import {selectors} from '#/main/authentication/administration/password-validate/store/selectors'
const PasswordValidateTool = (props) => {
  console.log(props)
  return (
      <FormData
        name={selectors.FORM_NAME}
        // name={"main_settings.authenticationParameters"}
        target={['apiv2_parameters_update']}
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
            fields: [
              {
                name: 'minLength',
                type: 'number',
                label: trans('minLength', {}, 'password-validate'),
              },
              {
                name: 'requireLowercase',
                type: 'boolean',
                label: trans('requireLowercase', {}, 'password-validate'),
              },
              {
                name: 'requireUppercase',
                type: 'boolean',
                label: trans('requireUppercase', {}, 'password-validate'),
              },
              {
                name: 'requireNumber',
                type: 'boolean',
                label: trans('requireNumber', {}, 'password-validate'),
              },
              {
                name: 'requireSpecialChar',
                type: 'boolean',
                label: trans('requireSpecialChar', {}, 'password-validate'),
              },
            ],
          }
        ]}
      />
  )
}

export {
  PasswordValidateTool
}
