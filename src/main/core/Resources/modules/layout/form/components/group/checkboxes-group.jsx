import React from 'react'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'

import {DataGroup as DataGroupTypes, DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'
import {FormGroup} from '#/main/app/content/form/components/group'
import {Checkboxes} from '#/main/core/layout/form/components/field/checkboxes'

/**
 * @param props
 * @constructor
 */
const CheckboxesGroup = props =>
  <FormGroup {...props}>
    <Checkboxes {...props} />
  </FormGroup>

implementPropTypes(CheckboxesGroup, [DataGroupTypes, DataInputTypes], {
  // more precise value type
  value: T.array,

  // custom props
  choices: T.object.isRequired,
  inline: T.bool
})

export {
  CheckboxesGroup
}
