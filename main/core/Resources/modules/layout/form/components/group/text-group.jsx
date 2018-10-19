import React from 'react'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'

import {FormGroupWithField as FormGroupWithFieldTypes} from '#/main/core/layout/form/prop-types'
import {FormGroup} from '#/main/core/layout/form/components/group/form-group'

import {Text} from '#/main/core/layout/form/components/field/text'

const TextGroup = props =>
  <FormGroup {...props}>
    <Text {...props} />
  </FormGroup>

implementPropTypes(TextGroup, FormGroupWithFieldTypes, {
  // more precise value type
  value: T.string,
  // custom props
  long: T.bool,
  minRows: T.number,
  minLength: T.number,
  maxLength: T.number
})

export {
  TextGroup
}
