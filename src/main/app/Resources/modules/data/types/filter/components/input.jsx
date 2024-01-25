import React from 'react'
import classes from 'classnames'
import get from 'lodash/get'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Select} from '#/main/app/input/components/select'

import {getPropDefinition} from '#/main/app/content/list/utils'
import {DataFilter} from '#/main/app/data/components/filter'

const FilterInput = props => {
  const filter = props.value || {}

  let searchProp = {}
  if (filter.property) {
    searchProp = getPropDefinition(filter.property, props.properties) || {}
  }

  return (
    <div className={classes('filter-control', props.className)}>
      <div className="filter-property">
        <Select
          id={`${props.id}-property`}
          value={filter.property}
          onChange={(column) => props.onChange(Object.assign({}, filter, {property: column}))}
          choices={props.properties.reduce((propList, current) => Object.assign(propList, {
            [current.alias || current.name]: current.label
          }), {})}
          size={props.size}
        />
      </div>

      <div className="filter-value">
        {filter.property &&
          <DataFilter
            id={props.id+'-value'}
            {...searchProp}
            value={filter.value}
            updateSearch={(value) => props.onChange(Object.assign({}, filter, {value: value}))}
          />
        }
      </div>

      <Button
        className="btn btn-text-secondary"
        type={CALLBACK_BUTTON}
        icon={classes('fa fa-fw', {
          'fa-lock': filter.locked,
          'fa-lock-open': !filter.locked
        })}
        label={trans(get(props, 'value.locked') ? 'click_to_unlock' : 'click_to_lock')}
        tooltip="bottom"
        size={props.size}
        callback={() => props.onChange(Object.assign({}, filter, {locked: !filter.locked}))}
      />
    </div>
  )
}

implementPropTypes(FilterInput, DataInputTypes, {
  value: T.shape({
    property: T.string,
    value: T.any, // depends on the type data type
    locked: T.bool
  }),

  // custom props
  properties: T.arrayOf(T.shape({ // TODO : use DataProp prop-types
    name: T.string.isRequired,
    alias: T.string,
    label: T.string.isRequired,
    type: T.string.isRequired
  }))
}, {
  value: {}
})

export {
  FilterInput
}
