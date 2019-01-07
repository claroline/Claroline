import React from 'react'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'

import {FormGroupWithField as FormGroupWithFieldTypes} from '#/main/core/layout/form/prop-types'
import {FormGroup} from '#/main/app/content/form/components/group.jsx'
import {Cascade} from '#/main/core/layout/form/components/field/cascade.jsx'

const CascadeGroup = props =>
  <FormGroup {...props}>
    <Cascade
      id={props.id}
      choices={props.choices}
      value={props.value}
      disabled={props.disabled}
      onChange={props.onChange}
    />
  </FormGroup>

implementPropTypes(CascadeGroup, FormGroupWithFieldTypes, {
  // more precise value type
  value: T.array,
  choices: T.array.isRequired
})

export {
  CascadeGroup
}
