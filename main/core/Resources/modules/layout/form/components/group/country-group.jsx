import React from 'react'
import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'

import {t} from '#/main/core/translation'

import {FormGroup as FormGroupWithFieldTypes} from '#/main/core/layout/form/prop-types'
import {FormGroup} from '#/main/core/layout/form/components/group/form-group.jsx'
import {Country} from '#/main/core/layout/form/components/field/country.jsx'

const CountryGroup = props =>
  <FormGroup
    {...props}
  >
    <Country
      {...props}
    />
  </FormGroup>

implementPropTypes(CountryGroup, FormGroupWithFieldTypes, {
  // more precise value type
  value: T.oneOfType([T.string, T.array]),
  // custom props
  multiple: T.bool,
  noEmpty: T.bool
}, {
  label: t('country'),
  multiple: false,
  noEmpty: false
})

export {
  CountryGroup
}
