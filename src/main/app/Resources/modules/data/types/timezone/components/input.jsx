import React from 'react'

import {constants} from '#/main/app/intl/date/constants'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'
import {Select} from '#/main/app/input/components/select'

const TimezoneInput = props =>
  <Select
    {...props}
    choices={constants.TIMEZONES}
  />

implementPropTypes(TimezoneInput, DataInputTypes, {
  value: T.string,
  noEmpty: T.bool
}, {
  value: ''
})

export {
  TimezoneInput
}
