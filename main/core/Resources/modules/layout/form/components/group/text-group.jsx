import React from 'react'
import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'

import {FormGroupWithField as FormGroupWithFieldTypes} from '#/main/core/layout/form/prop-types'
import {FormGroup} from '#/main/core/layout/form/components/group/form-group.jsx'
import {Text} from '#/main/core/layout/form/components/field/text.jsx'

const TextGroup = props =>
  <FormGroup {...props}>
    <Text
      id={props.id}
      value={props.value}
      disabled={props.disabled}
      onChange={props.onChange}
      long={props.long}
      minRows={props.minRows}
      minLength={props.minLength}
      maxLength={props.maxLength}
    />
  </FormGroup>

implementPropTypes(TextGroup, FormGroupWithFieldTypes, {
  // more precise value type
  value: T.string,
  // custom props
  long: T.bool,
  minRows: T.number,
  minLength: T.number, // todo implement
  maxLength: T.number // todo implement
}, {
  value: '',
  placeholder: '',
  long: false,
  minRows: 2
})

export {
  TextGroup
}
