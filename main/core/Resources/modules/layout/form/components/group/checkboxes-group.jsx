import React from 'react'
import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'

import {FormGroupWithField as FormGroupWithFieldTypes} from '#/main/core/layout/form/prop-types'
import {FormGroup} from '#/main/core/layout/form/components/group/form-group'
import {Checkboxes} from '#/main/core/layout/form/components/field/checkboxes'

/**
 * @param props
 * @constructor
 */
const CheckboxesGroup = props =>
  <FormGroup {...props}>
    <Checkboxes {...props} />
  </FormGroup>

implementPropTypes(CheckboxesGroup, FormGroupWithFieldTypes, {
  // more precise value type
  value: T.array,

  // custom props
  choices: T.object.isRequired,
  inline: T.bool
})

export {
  CheckboxesGroup
}
