import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {FormData} from '#/main/app/content/form/containers/data'

const SimpleWidgetParameters = (props) =>
  <FormData
    embedded={true}
    level={5}
    flush={true}
    name={props.name}
    definition={[
      {
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'parameters.content',
            label: trans('content'),
            type: 'html',
            required: true,
            hideLabel: true,
            options: {
              minimal: false,
              workspace: 'workspace' === props.currentContext.type ? props.currentContext.data : undefined
            }
          }
        ]
      }
    ]}
  />

SimpleWidgetParameters.propTypes = {
  name: T.string.isRequired,
  currentContext: T.shape({
    type: T.string,
    data: T.object
  }).isRequired
}

export {
  SimpleWidgetParameters
}
