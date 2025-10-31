import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {FormContent} from '#/main/app/content/form/containers/content'

const ResourceWidgetParameters = (props) =>
  <FormContent
    flush={true}
    level={5}
    name={props.name}
    definition={[
      {
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'parameters.resource',
            label: trans('resource'),
            type: 'resource',
            displayed: (data) => 'personal_workspace' !== get(data, 'source'),
            required: true,
            options: {
              picker: {
                contextId: props.contextData ? props.contextData.id : null
              }
            }
          }, {
            name: 'parameters.showResourceHeader',
            type: 'boolean',
            label: trans('show_resource_header', {}, 'resource')
          }
        ]
      }
    ]}
  />

ResourceWidgetParameters.propTypes = {
  name: T.string.isRequired,
  contextType: T.string.isRequired,
  contextData: T.object
}

export {
  ResourceWidgetParameters
}
