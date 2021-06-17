import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {ListData} from '#/main/app/content/list/containers/data'

const FunctionalLogList = (props) =>
  <ListData
    name={props.name}
    fetch={{
      url: props.url,
      autoload: true
    }}
    definition={[
      {
        name: 'user',
        type: 'user',
        label: trans('user'),
        displayed: true
      }, {
        name: 'date',
        label: trans('date'),
        type: 'date',
        options: {time: true},
        displayed: true
      }, {
        name: 'details',
        type: 'string',
        label: trans('description'),
        displayed: true
      }, {
        name: 'resource',
        type: 'resource',
        label: trans('resource'),
        displayed: true
      }, {
        name: 'workspace',
        type: 'workspace',
        label: trans('workspace'),
        displayed: true
      }, {
        name: 'event',
        type: 'translation',
        label: trans('event'),
        displayed: false,
        options: {
          domain: 'security'
        }
      }
    ]}
  />

FunctionalLogList.propTypes = {
  name: T.string.isRequired,
  url: T.oneOfType([T.string, T.array]),
  definition: T.array
}

FunctionalLogList.defaultProps = {
  url: ['apiv2_logs_functional_list_current'],
  definition: []
}

export {
  FunctionalLogList
}
