import React from 'react'

import {trans} from '#/main/core/translation'
import {FormData} from '#/main/app/content/form/containers/data'

const SimpleWidgetParameters = (props) =>
  <FormData
    level={5}
    name={props.name}
    sections={[
      {
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'parameters.content',
            label: trans('content'),
            type: 'html',
            required: true
          }
        ]
      }
    ]}
  />

export {
  SimpleWidgetParameters
}
