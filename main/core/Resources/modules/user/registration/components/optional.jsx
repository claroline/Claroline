import React from 'react'

import {t} from '#/main/core/translation'

import {FormContainer} from '#/main/core/data/form/containers/form.jsx'

/**
 * Registration Form : Optional section.
 * Contains optional configuration fields for the user registration.
 *
 * @constructor
 */
const Optional = () =>
  <FormContainer
    level={2}
    name="user"
    sections={[
      {
        title: t('general'),
        primary: true,
        fields: [
          {
            name: 'preferences.locale',
            type: 'locale',
            label: t('language'),
            options: {
              onlyEnabled: true
            }
          }, {
            name: 'meta.description',
            type: 'html',
            label: t('description')
          }, {
            name: 'picture',
            type: 'image',
            label: t('picture')
          }
        ]
      }
    ]}
  />

export {
  Optional
}
