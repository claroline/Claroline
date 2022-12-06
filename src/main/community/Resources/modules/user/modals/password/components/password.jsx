import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {User as UserType} from '#/main/community/prop-types'
import {FormDataModal} from '#/main/app/modals/form/components/data'

const PasswordModal = props =>
  <FormDataModal
    {...props}
    icon="fa fa-fw fa-lock"
    title={trans('change_password')}
    save={(data) => props.changePassword(props.user, data.plainPassword)}
    definition={[
      {
        id: 'general',
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'plainPassword',
            type: 'password',
            label: trans('password'),
            required: true
          }
        ]
      }
    ]}
  />

PasswordModal.propTypes = {
  user: T.shape(UserType.propTypes),
  changePassword: T.func.isRequired
}

export {
  PasswordModal
}
