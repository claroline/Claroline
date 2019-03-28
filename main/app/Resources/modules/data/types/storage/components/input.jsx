import React from 'react'
import classes from 'classnames'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {Button} from '#/main/app/action/components/button'
import {MENU_BUTTON, CALLBACK_BUTTON} from '#/main/app/buttons'
import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'

import {NumberInput} from '#/main/app/data/types/number/components/input'

// TODO : finish implementation
// TODO : make it more generic by implementing a number with unit type

const STORAGE_UNITS = {
  B: 'B',
  KB: 'KB',
  MB: 'MB',
  GB: 'GB',
  TB: 'TB'
}

const StorageInput = props =>
  <div className={classes('input-group', props.className, {
    [`input-group-${props.size}`]: !!props.size
  })}>
    <NumberInput
      {...omit(props, 'className')}
      value={props.value}
    />

    <span className="input-group-btn">
      <Button
        id={`units-${props.id}`}
        className="btn"
        type={MENU_BUTTON}
        label="KB"
        menu={{
          label: trans('units'),
          align: 'right',
          items: Object.keys(STORAGE_UNITS).map(unit => ({
            type: CALLBACK_BUTTON,
            label: STORAGE_UNITS[unit],
            callback: () => {
              //props.onChange(format)
            }
          }))
        }}
      />
    </span>
  </div>

implementPropTypes(StorageInput, FormFieldTypes, {
  // more precise value type
  value: T.number
})

export {
  StorageInput
}
