import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {FormData} from '#/main/app/content/form/containers/data'

/**
 * Registration Form : Required section.
 * Contains all fields required for the user registration.
 *
 * @constructor
 */
const Required = () =>
  <FormData
    level={2}
    name="user"
    sections={[
      {
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'lastName',
            type: 'string',
            label: trans('last_name'),
            required: true
          }, {
            name: 'firstName',
            type: 'string',
            label: trans('first_name'),
            required: true
          }, {
            name: 'email',
            type: 'email',
            label: trans('email'),
            required: true
          }, {
            name: 'username',
            type: 'username',
            label: trans('username'),
            required: true
          }, {
            name: 'plainPassword',
            type: 'password',
            label: trans('password'),
            required: true
          }
        ]
      }
    ]}
  />

export {
  Required
}
