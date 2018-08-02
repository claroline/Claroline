import React from 'react'

import {trans} from '#/main/core/translation'

import {FormData} from '#/main/app/content/form/containers/data'

/**
 * @constructor
 */
const Organization = () =>
  <FormData
    level={2}
    name="user"
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
