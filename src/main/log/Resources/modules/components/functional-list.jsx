import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl'
import {ListData} from '#/main/app/content/list/containers/data'

const LogFunctionalList = (props) =>
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
      }
    ].concat(props.customDefinition)}
    selectable={false}
  />

LogFunctionalList.propTypes = {
  name: T.string.isRequired,
  url: T.oneOfType([T.string, T.array]).isRequired,
  customDefinition: T.arrayOf(T.shape({
    // data list prop types
  }))
}

LogFunctionalList.defaultProps = {
  customDefinition: []
}

export {
  LogFunctionalList
}
