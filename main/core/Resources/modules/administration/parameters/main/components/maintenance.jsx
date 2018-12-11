import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {URL_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'

const Maintenance = () =>
  <FormData
    level={2}
    title={trans('maintenance')}
    name="parameters"
    target={['apiv2_parameters_update']}
    buttons={true}
    cancel={{
      type: URL_BUTTON,
      target: ['claro_admin_open']
    }}
    sections={[
      {
        title: trans('general'),
        fields: [
          {
            name: 'maintenance.enable',
            type: 'boolean',
            label: trans('enable'),
            required: false
          }, {
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
