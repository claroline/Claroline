import React from 'react'
import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'

import {Numeric} from '#/main/core/layout/form/components/field/numeric.jsx'
import {FormGroup as FormGroupWithFieldTypes} from '#/main/core/layout/form/prop-types'
import {FormGroup} from '#/main/core/layout/form/components/group/form-group.jsx'

const NumberGroup = props =>
  <FormGroup {...props}>
    <Numeric {...props} />
  </FormGroup>

implementPropTypes(NumberGroup, FormGroupWithFieldTypes, {
  // more precise value type
  value: T.number,
  // custom props
  min: T.number,
  max: T.number,
  unit: T.string
})

export {
  NumberGroup
}
