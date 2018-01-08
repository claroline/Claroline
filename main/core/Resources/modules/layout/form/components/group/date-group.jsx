import React from 'react'
import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'

import {FormGroup as FormGroupWithFieldTypes} from '#/main/core/layout/form/prop-types'
import {FormGroup} from '#/main/core/layout/form/components/group/form-group.jsx'
import {DatePicker} from '#/main/core/layout/form/components/field/date-picker.jsx'

const DateGroup = props =>
  <FormGroup
    {...props}
  >
    <DatePicker
      controlId={props.controlId}
      dateFormat={props.dateFormat}
      minDate={props.minDate}
      value={props.value || ''}
      showCalendarButton={props.showCalendarButton}
      disabled={props.disabled}
      onChange={props.onChange}
    />
  </FormGroup>

implementPropTypes(DateGroup, FormGroupWithFieldTypes, {
  // more precise value type
  value: T.string,
  // custom props
  dateFormat: T.string,
  minDate: T.object,
  showCalendarButton: T.bool
}, {
  value: '',
  showCalendarButton: false
})

export {
  DateGroup
}
