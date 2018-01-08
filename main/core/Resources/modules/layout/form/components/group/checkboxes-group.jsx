import React from 'react'
import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'

import {FormGroup as FormGroupWithFieldTypes} from '#/main/core/layout/form/prop-types'
import {FormGroup} from '#/main/core/layout/form/components/group/form-group.jsx'
import {Checkboxes} from '#/main/core/layout/form/components/field/checkboxes.jsx'

/**
 * @param props
 * @constructor
 */
const CheckboxesGroup = props =>
  <FormGroup {...props}>
    <Checkboxes
      id={props.id}
      inline={props.inline}
      options={props.options}
      checkedValues={props.value || []}
      disabled={props.disabled}
      onChange={props.onChange}
    />
  </FormGroup>

implementPropTypes(CheckboxesGroup, FormGroupWithFieldTypes, {
  // more precise value type
  value: T.array,

  // custom props
  options: T.array.isRequired,
  inline: T.bool
}, {
  value: [],
  inline: true
})

export {
  CheckboxesGroup
}
