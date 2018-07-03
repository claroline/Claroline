import React from 'react'

import {trans} from '#/main/core/translation'
import {FormContainer} from '#/main/core/data/form/containers/form'
import {selectors} from '#/main/core/widget/content/modals/creation/store'

const SimpleWidgetParameters = () =>
  <FormContainer
    level={5}
    name={selectors.FORM_NAME}
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
