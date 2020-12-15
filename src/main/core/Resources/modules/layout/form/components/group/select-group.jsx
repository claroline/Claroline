import React from 'react'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'

import {DataGroup as DataGroupTypes, DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'
import {FormGroup} from '#/main/app/content/form/components/group'
import {Select} from '#/main/core/layout/form/components/field/select'

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

implementPropTypes(SelectGroup, [DataGroupTypes, DataInputTypes], {
  // more precise value type
  value: T.oneOfType([T.string, T.number, T.array, T.bool]),
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
