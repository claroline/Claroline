import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {FormData} from '#/main/app/content/form/containers/data'

import {selectors} from '#/main/app/security/registration/store/selectors'

const Organization = () =>
  <FormData
    level={2}
    name={selectors.FORM_NAME}
    sections={[
      {
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'mainOrganization.name',
            type: 'string',
            label: trans('name'),
            required: true
          }, {
            name: 'mainOrganization.code',
            type: 'string',
            label: trans('code'),
            required: true
          }, {
            name: 'mainOrganization.vat',
            label: trans('vat_number'),
            type: 'string',
            required: false
          }, {
            name: 'mainOrganization.email',
            type: 'email',
            label: trans('email')
          }
        ]
      }
    ]}
  />

export {
  Organization
}
