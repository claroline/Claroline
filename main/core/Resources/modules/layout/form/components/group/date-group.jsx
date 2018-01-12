import React from 'react'
import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'

import {FormGroup as FormGroupWithFieldTypes} from '#/main/core/layout/form/prop-types'
import {FormGroup} from '#/main/core/layout/form/components/group/form-group.jsx'
import {Date} from '#/main/core/layout/form/components/field/date.jsx'

const DateGroup = props =>
  <FormGroup
    {...props}
  >
    <Date
      id={props.id}
      minDate={props.minDate}
      maxDate={props.maxDate}
      value={props.value}
      onlyButton={props.onlyButton}
      disabled={props.disabled}
      onChange={props.onChange}
    />
  </FormGroup>

implementPropTypes(DateGroup, FormGroupWithFieldTypes, {
  // more precise value type
  value: T.string,
  // custom props
  minDate: T.object,
  maxDate: T.object,
  onlyButton: T.bool
}, {
  value: '',
  onlyButton: false
})

export {
  DateGroup
}
