import React from 'react'
import classes from 'classnames'
import get from 'lodash/get'

import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'
import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'

import {trans} from '#/main/core/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Select} from '#/main/core/layout/form/components/field/select'

import {SearchProp} from '#/main/app/content/search/components/prop'

const FilterInput = props => {
  const filter = props.value || {}

  let searchProp = {}
  if (filter.property) {
    searchProp = props.properties.find(prop => prop.name === filter.property) || {}
  }

  return (
    <div className="filter-control">
      <div className="filter-property">
        <Select
          id={`${props.id}-property`}
          value={filter.property}
          onChange={(column) => props.onChange(Object.assign({}, filter, {property: column}))}
          choices={props.properties.reduce((propList, current) => Object.assign(propList, {[current.name]: current.label}), {})}
          size={props.size}
        />
      </div>

      <div className="filter-value">
        {filter.property &&
          <SearchProp
            {...searchProp}
            currentSearch={filter.value}
            updateSearch={(value) => props.onChange(Object.assign({}, filter, {value: value}))}
          />
        }
      </div>

      <Button
        className="btn"
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

implementPropTypes(FilterInput, FormFieldTypes, {
  value: T.shape({
    property: T.string,
    value: T.any, // depends on the type data type
    locked: T.bool
  }),

  // custom props
  properties: T.arrayOf(T.shape({
    name: T.string.isRequired,
    label: T.string.isRequired,
    type: T.string.isRequired
  }))
}, {
  value: {}
})

export {
  FilterInput
}
