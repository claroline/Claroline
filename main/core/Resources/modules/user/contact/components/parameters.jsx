import React from 'react'

import {trans} from '#/main/core/translation'
import {FormData} from '#/main/app/content/form/containers/data'

const Parameters = () =>
  <FormData
    level={3}
    name="options"
    buttons={true}
    target={(parameters) => ['apiv2_contact_options_update', {id: parameters.id}]}
    sections={[
      {
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'data.show_username',
            type: 'boolean',
            label: trans('show_username')
          }, {
            name: 'data.show_mail',
            type: 'boolean',
            label: trans('show_mail')
          }, {
            name: 'data.show_phone',
            type: 'boolean',
            label: trans('show_phone')
          }
        ]
      }
    ]}
  />

export {
  Parameters
}
