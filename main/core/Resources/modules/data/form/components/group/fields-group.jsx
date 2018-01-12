import React from 'react'
import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'

import {FormGroup as FormGroupWithFieldTypes} from '#/main/core/layout/form/prop-types'
import {FormGroup} from '#/main/core/layout/form/components/group/form-group.jsx'

import {Fields} from '#/main/core/data/form/components/field/fields.jsx'

const FieldsGroup = props =>
  <FormGroup
    {...props}
  >
    <Fields
      {...props}
    />
  </FormGroup>

implementPropTypes(FieldsGroup, FormGroupWithFieldTypes, {
  // more precise value type
  value: T.array,
  // custom props
  min: T.number
}, {
  value: []
})

export {
  FieldsGroup
}
