import React from 'react'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'

import {DataGroup as DataGroupTypes, DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'
import {FormGroup} from '#/main/app/content/form/components/group.jsx'
import {Date} from '#/main/core/layout/form/components/field/date.jsx'

const DateGroup = props =>
  <FormGroup {...props}>
    <Date {...props} />
  </FormGroup>

implementPropTypes(DateGroup, [DataGroupTypes, DataInputTypes], {
  // more precise value type
  value: T.string,
  calendarIcon: T.string,

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
