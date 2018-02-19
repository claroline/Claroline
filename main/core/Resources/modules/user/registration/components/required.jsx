import React from 'react'

import {t} from '#/main/core/translation'

import {FormContainer} from '#/main/core/data/form/containers/form.jsx'

/**
 * Registration Form : Required section.
 * Contains all fields required for the user registration.
 *
 * @constructor
 */
const Required = () =>
  <FormContainer
    level={2}
    name="user"
    sections={[
      {
        id: 'general',
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
