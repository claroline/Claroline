import React from 'react'

import {FormData} from '#/main/app/content/form/containers/data'
import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {selectors} from '#/main/authentication/administration/password-validate/store/selectors'

const PasswordValidateTool = (props) => {
  console.log('props', props)
  return (
      <FormData
        name={selectors.STORE_NAME}
        target={['apiv2_parameters_update']}
        buttons={true}
        cancel={{
          type: LINK_BUTTON,
          target: props.path,
          exact: true
        }}
        locked={props.lockedParameters}
        definition={[
          {
            title: trans('password-validate', {}, 'tools'),
            fields: [
              {
                name: 'passwordValidate.minLength',
                type: 'number',
                label: trans('minLength', {}, 'tools'),
              },
              {
                name: 'passwordValidate.requireLowercase',
                type: 'boolean',
                label: trans('requireLowercase', {}, 'tools'),
              },
              {
                name: 'passwordValidate.requireUppercase',
                type: 'boolean',
                label: trans('requireUppercase', {}, 'tools'),
              },
              {
                name: 'passwordValidate.requireNumber',
                type: 'boolean',
                label: trans('requireNumber', {}, 'tools'),
              },
              {
                name: 'passwordValidate.requireSpecialChar',
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
