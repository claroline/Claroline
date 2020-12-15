import React from 'react'

import {constants as intlConstants} from '#/main/app/intl/constants'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'
import {Select} from '#/main/app/input/components/select'

const CountryInput = props =>
  <Select
    {...props}
    choices={intlConstants.REGIONS}
  />

implementPropTypes(CountryInput, DataInputTypes, {
  value: T.oneOfType([T.string, T.array]),
  multiple: T.bool,
  noEmpty: T.bool
}, {
  value: '',
  multiple: false,
  noEmpty: false
})

export {
  CountryInput
}
