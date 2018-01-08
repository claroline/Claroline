import React from 'react'

import {constants as intlConstants} from '#/main/core/intl/constants'

import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'
import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'
import {Select} from '#/main/core/layout/form/components/field/select.jsx'

const Country = props =>
  <Select
    {...props}
    choices={intlConstants.REGIONS}
  />

implementPropTypes(Country, FormFieldTypes, {
  value: T.oneOfType([T.string, T.array]),
  multiple: T.bool,
  noEmpty: T.bool
}, {
  value: '',
  multiple: false,
  noEmpty: false
})

export {
  Country
}
