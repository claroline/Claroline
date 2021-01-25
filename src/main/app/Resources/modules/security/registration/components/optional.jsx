import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {FormData} from '#/main/app/content/form/containers/data'

import {selectors} from '#/main/app/security/registration/store/selectors'

/**
 * Registration Form : Optional section.
 * Contains optional configuration fields for the user registration.
 */
const Optional = () =>
  <FormData
    level={2}
    name={selectors.FORM_NAME}
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
            name: 'phone',
            type: 'string',
            label: trans('phone')
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
