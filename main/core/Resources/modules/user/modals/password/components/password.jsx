import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/core/translation'
import {DataFormModal} from '#/main/core/data/form/modals/components/data-form'

const PasswordModal = props =>
  <DataFormModal
    {...props}
    icon="fa fa-fw fa-lock"
    title={trans('change_password')}
    save={(data) => props.changePassword(data.plainPassword)}
    sections={[
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
  changePassword: T.func.isRequired
}

export {
  PasswordModal
}
