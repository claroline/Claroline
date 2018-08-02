import React from 'react'

import {t} from '#/main/core/translation'

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
        title: t('general'),
        primary: true,
        fields: [
          {
            name: 'lastName',
            type: 'string',
            label: t('last_name'),
            required: true
          }, {
            name: 'firstName',
            type: 'string',
            label: t('first_name'),
            required: true
          }, {
            name: 'email',
            type: 'email',
            label: t('email'),
            required: true
          }, {
            name: 'username',
            type: 'username',
            label: t('username'),
            required: true
          }, {
            name: 'plainPassword',
            type: 'password',
            label: t('password'),
            required: true
          }
        ]
      }
    ]}
  />

export {
  Required
}
