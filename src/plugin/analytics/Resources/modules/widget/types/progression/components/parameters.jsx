import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {FormData} from '#/main/app/content/form/containers/data'

const ProgressionWidgetParameters = (props) =>
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
            name: 'parameters.levelMax',
            label: trans('depth_in_resources_directories'),
            type: 'number',
            required: false,
            options: {
              min: 0
            }
          }
        ]
      }
    ]}
  />

ProgressionWidgetParameters.propTypes = {
  name: T.string.isRequired
}

export {
  ProgressionWidgetParameters
}
