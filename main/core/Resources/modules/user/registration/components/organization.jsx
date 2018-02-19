import React from 'react'

import {trans} from '#/main/core/translation'

import {FormContainer} from '#/main/core/data/form/containers/form.jsx'

/**
 * @constructor
 */
const Organization = () =>
  <FormContainer
    level={2}
    name="user"
    sections={[
      {
        id: 'general',
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
