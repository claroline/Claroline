import React from 'react'

import {trans} from '#/main/core/translation'
import {FormContainer} from '#/main/core/data/form/containers/form'

const SimpleWidgetParameters = (props) =>
  <FormContainer
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
