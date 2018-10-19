import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {FormData} from '#/main/app/content/form/containers/data'

const SimpleWidgetParameters = (props) =>
  <FormData
    embedded={true}
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
            required: true,
            options: {
              workspace: 'workspace' === props.context.type ? props.context.data : undefined,
              minimal: false
            }
          }
        ]
      }
    ]}
  />

SimpleWidgetParameters.propTypes = {
  name: T.string.isRequired,
  context: T.shape({
    type: T.string,
    data: T.object
  })
}

export {
  SimpleWidgetParameters
}
