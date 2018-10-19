import React from 'react'

import {trans} from '#/main/app/intl/translation'

import {FormData} from '#/main/app/content/form/containers/data'

/**
 * Registration Form : Optional section.
 * Contains optional configuration fields for the user registration.
 *
 * @constructor
 */
const Optional = () =>
  <FormData
    level={2}
    name="user"
    sections={[
      {
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'preferences.locale',
            type: 'locale',
            label: trans('language'),
            options: {
              onlyEnabled: true
            }
          }, {
            name: 'meta.description',
            type: 'html',
            label: trans('description')
          }, {
            name: 'picture',
            type: 'image',
            label: trans('picture')
          }
        ]
      }
    ]}
  />

export {
  Optional
}
