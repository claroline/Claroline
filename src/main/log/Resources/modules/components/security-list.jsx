import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl'
import {ListData} from '#/main/app/content/list/containers/data'

const LogSecurityList = (props) =>
  <ListData
    {...omit(props, 'url', 'name', 'customDefinition')}

    name={props.name}
    fetch={{
      url: props.url,
      autoload: true
    }}

    definition={[
      {
        name: 'date',
        label: trans('date'),
        type: 'date',
        options: {time: true},
        displayed: true
      }, {
        name: 'event',
        type: 'translation',
        label: trans('event'),
        displayed: false,
        options: {
          domain: 'log'
        }
      }, {
        name: 'doer',
        type: 'user',
        label: trans('user'),
        displayed: true
      }, {
        name: 'details',
        type: 'html',
        label: trans('description'),
        displayed: true,
        options: {trust: true}
      }, {
        name: 'target',
        type: 'user',
        label: trans('target'),
        displayed: false
      }, {
        name: 'doer_ip',
        label: trans('ip_address'),
        type: 'ip',
        displayed: false
      }
    ].concat(props.customDefinition)}
    selectable={false}
  />

LogSecurityList.propTypes = {
  name: T.string.isRequired,
  url: T.oneOfType([T.string, T.array]).isRequired,
  customDefinition: T.arrayOf(T.shape({
    // data list prop types
  }))
}

LogSecurityList.defaultProps = {
  customDefinition: []
}

export {
  LogSecurityList
}
