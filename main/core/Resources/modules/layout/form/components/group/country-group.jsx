import React from 'react'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'

import {trans} from '#/main/app/intl/translation'

import {FormGroupWithField as FormGroupWithFieldTypes} from '#/main/core/layout/form/prop-types'
import {FormGroup} from '#/main/core/layout/form/components/group/form-group'
import {Country} from '#/main/core/layout/form/components/field/country'

const CountryGroup = props =>
  <FormGroup {...props}>
    <Country {...props} />
  </FormGroup>

implementPropTypes(CountryGroup, FormGroupWithFieldTypes, {
  // more precise value type
  value: T.oneOfType([T.string, T.array]),
  // custom props
  multiple: T.bool,
  noEmpty: T.bool
}, {
  label: trans('country'),
  multiple: false,
  noEmpty: false
})

export {
  CountryGroup
}
