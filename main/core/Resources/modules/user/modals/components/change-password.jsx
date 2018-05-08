import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/core/translation'
import {DataFormModal} from '#/main/core/data/form/modals/components/data-form'

const MODAL_CHANGE_PASSWORD = 'MODAL_CHANGE_PASSWORD'

const ChangePasswordModal = props =>
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

ChangePasswordModal.propTypes = {
  changePassword: T.func.isRequired
}

export {
  MODAL_CHANGE_PASSWORD,
  ChangePasswordModal
}
