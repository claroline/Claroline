import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'

import {route as adminRoute} from '#/main/core/administration/routing'
import {selectors} from '#/main/core/administration/parameters/main/store'

const Maintenance = () =>
  <FormData
    level={2}
    name={selectors.FORM_NAME}
    target={['apiv2_parameters_update']}
    buttons={true}
    cancel={{
      type: LINK_BUTTON,
      target: adminRoute('main_settings'),
      exact: true
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
