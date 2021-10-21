import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {FormData} from '#/main/app/content/form/containers/data'

import {selectors} from '#/main/app/security/registration/store/selectors'

const OrganizationSelection = () =>
  <FormData
    level={2}
    name={selectors.FORM_NAME}
    sections={[
      {
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'mainOrganization',
            type: 'organization',
            label: trans('organization'),
            required: true,
            hideLabel: true,
            options: {
              mode: 'choice'
            }
          }
        ]
      }
    ]}
  />

export {
  OrganizationSelection
}
