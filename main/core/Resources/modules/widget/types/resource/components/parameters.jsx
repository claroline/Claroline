import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {FormData} from '#/main/app/content/form/containers/data'

const ResourceWidgetParameters = (props) =>
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
            name: 'parameters.resource',
            label: trans('resource'),
            type: 'resource',
            displayed: (data) => 'personal_workspace' !== get(data, 'source'),
            required: true
          }, {
            name: 'parameters.showResourceHeader',
            type: 'boolean',
            label: trans('show_resource_header')
          }
        ]
      }
    ]}
  />

ResourceWidgetParameters.propTypes = {
  name: T.string.isRequired
}

export {
  ResourceWidgetParameters
}
