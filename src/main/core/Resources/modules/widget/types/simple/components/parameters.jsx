import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {FormContent} from '#/main/app/content/form/containers/content'

const SimpleWidgetParameters = (props) =>
  <FormContent
    level={5}
    flush={true}
    name={props.name}
    definition={[
      {
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'parameters.contentRaw',
            label: trans('content'),
            type: 'html',
            required: true,
            hideLabel: true,
            options: {
              minimal: false,
              workspace: 'workspace' === props.currentContext.type ? props.currentContext.data : undefined,
              config: {
                plugins: ['placeholders'],
                placeholders: get(props.instance, 'parameters.placeholders', [])
              }
            }
          }
        ]
      }
    ]}
  />

SimpleWidgetParameters.propTypes = {
  name: T.string.isRequired,
  instance: T.shape({
    content: T.string,
    contentRaw: T.string,
    placeholders: T.arrayOf(T.string),
  }),
  currentContext: T.shape({
    type: T.string,
    data: T.object
  }).isRequired
}

export {
  SimpleWidgetParameters
}
