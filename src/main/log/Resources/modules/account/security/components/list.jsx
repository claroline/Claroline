import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {ListData} from '#/main/app/content/list/containers/data'

const SecurityLogList = (props) =>
  <ListData
    name={props.name}
    fetch={{
      url: props.url,
      autoload: true
    }}
    definition={[
      {
        name: 'doer',
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
        name: 'target',
        type: 'user',
        label: trans('target'),
        displayed: false
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

SecurityLogList.propTypes = {
  name: T.string.isRequired,
  url: T.oneOfType([T.string, T.array]),
  definition: T.array
}

SecurityLogList.defaultProps = {
  url: ['apiv2_logs_security_list_current'],
  definition: []
}

export {
  SecurityLogList
}
