import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {URL_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'

const Meta = () =>
  <FormData
    level={2}
    title={trans('information')}
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
            name: 'display.name',
            type: 'string',
            label: trans('name')
          }, {
            name: 'display.secondary_name',
            type: 'string',
            label: trans('secondary_name'),
            required: false
          }
        ]
      }
    ]}
  />

export {
  Meta
}
