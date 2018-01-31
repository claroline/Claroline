import React from 'react'
import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'

import {FormGroupWithField as FormGroupWithFieldTypes} from '#/main/core/layout/form/prop-types'
import {FormGroup} from '#/main/core/layout/form/components/group/form-group.jsx'
import {Date} from '#/main/core/layout/form/components/field/date.jsx'

const DateGroup = props =>
  <FormGroup {...props}>
    <Date {...props} />
  </FormGroup>

implementPropTypes(DateGroup, FormGroupWithFieldTypes, {
  // more precise value type
  value: T.string,

  // date configuration
  minDate: T.string,
  maxDate: T.string,

  // time configuration
  time: T.bool,
  minTime: T.string,
  maxTime: T.string
}, {
  value: ''
})

export {
  DateGroup
}
