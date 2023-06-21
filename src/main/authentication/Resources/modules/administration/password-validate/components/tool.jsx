import React from 'react'

import {FormData} from '#/main/app/content/form/containers/data'
import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {selectors} from '#/main/authentication/administration/password-validate/store/selectors'

const PasswordValidateTool = (props) => {
  console.log('props', props)
  return (
      <FormData
        // name={selectors.STORE_NAME}
        name={"main_settings.authenticationParameters"}
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
                label: trans('minLength', {}, 'tools'),
              },
              {
                name: 'requireLowercase',
                type: 'boolean',
                label: trans('requireLowercase', {}, 'tools'),
              },
              {
                name: 'requireUppercase',
                type: 'boolean',
                label: trans('requireUppercase', {}, 'tools'),
              },
              {
                name: 'requireNumber',
                type: 'boolean',
                label: trans('requireNumber', {}, 'tools'),
              },
              {
                name: 'requireSpecialChar',
                type: 'boolean',
                label: trans('requireSpecialChar', {}, 'tools'),
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
