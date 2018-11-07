import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'

const Maintenance = () =>
  <FormData
    name="parameters"
    target={['apiv2_parameters_update']}
    buttons={true}
    cancel={{
      type: LINK_BUTTON,
      target: '/main',
      exact: true
    }}
    sections={[
      {
        icon: 'fa fa-fw fa-user-plus',
        title: trans('maintenance'),
        fields: [
          {
            name: 'maintenance.enable',
            type: 'boolean',
            label: trans('enable'),
            required: false
          },
          {
            name: 'maintenance.message',
            type: 'html',
            label: trans('content'),
            required: false,
            options: {
              long: true
            }
          }
        ]
      }
    ]}
  />

export {
  Maintenance
}
