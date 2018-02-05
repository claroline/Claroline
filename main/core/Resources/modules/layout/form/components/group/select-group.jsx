import React from 'react'
import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'

import {FormGroupWithField as FormGroupWithFieldTypes} from '#/main/core/layout/form/prop-types'
import {FormGroup} from '#/main/core/layout/form/components/group/form-group.jsx'
import {Select} from '#/main/core/layout/form/components/field/select.jsx'

const SelectGroup = props =>
  <FormGroup {...props}>
    <Select
      id={props.id}
      choices={props.choices}
      value={props.value}
      disabled={props.disabled}
      onChange={props.onChange}
      multiple={props.multiple}
      noEmpty={props.noEmpty}
    />
  </FormGroup>

implementPropTypes(SelectGroup, FormGroupWithFieldTypes, {
  // more precise value type
  value: T.oneOfType([T.string, T.number, T.array]),
  // custom props
  choices: T.object.isRequired,
  multiple: T.bool,
  noEmpty: T.bool
}, {
  multiple: false,
  noEmpty: false
})

export {
  SelectGroup
}
