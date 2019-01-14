import React from 'react'
import pick from 'lodash/pick'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'

import {
  FormField as FormFieldTypes,
  FormGroup as FormGroupTypes,
  FormGroupWithField as FormGroupWithFieldTypes
} from '#/main/core/layout/form/prop-types'
import {FormGroup} from '#/main/app/content/form/components/group'

import {Text} from '#/main/core/layout/form/components/field/text'

const TextGroup = props =>
  <FormGroup {...pick(props, Object.keys(FormGroupTypes.propTypes))}>
    <Text {...pick(props, [...Object.keys(FormFieldTypes.propTypes), 'long', 'minRows', 'minLength', 'maxLength'])} />
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
