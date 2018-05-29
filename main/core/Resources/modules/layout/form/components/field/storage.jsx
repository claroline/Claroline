import React from 'react'

import {trans} from '#/main/core/translation'
import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'
import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'
import {DropdownButton, MenuItem} from '#/main/core/layout/components/dropdown'

import {Numeric} from '#/main/core/layout/form/components/field/numeric'

// TODO : finish implementation
// TODO : make it more generic by implementing a number with unit type

const STORAGE_UNITS = {
  B: 'B',
  KB: 'KB',
  MB: 'MB',
  GB: 'GB',
  TB: 'TB'
}

const Storage = props =>
  <div className="input-group">
    <Numeric
      {...props}
      value={props.value}
    />

    <span className="input-group-btn">
      <DropdownButton
        id={`units-${props.id}`}
        title="KB"
        bsStyle="default"
        noCaret={true}
        pullRight={true}
        dropUp={true}
      >
        <MenuItem header>{trans('units')}</MenuItem>

        {Object.keys(STORAGE_UNITS).map(unit =>
          <MenuItem
            key={unit}
            eventKey={unit}
            active={false}
            onClick={(e) => {
              e.preventDefault()
              e.stopPropagation()
              e.target.blur()
              //props.onChange(format)
            }}
          >
            {STORAGE_UNITS[unit]}
          </MenuItem>
        )}
      </DropdownButton>
    </span>
  </div>

implementPropTypes(Storage, FormFieldTypes, {
  // more precise value type
  value: T.number
})

export {
  Storage
}
