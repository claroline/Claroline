import React from 'react'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'

import {DataGroup as DataGroupTypes, DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'
import {FormGroup} from '#/main/app/content/form/components/group'
import {Numeric} from '#/main/core/layout/form/components/field/numeric'

const NumberGroup = props =>
  <FormGroup {...props}>
    <Numeric {...props} />
  </FormGroup>

implementPropTypes(NumberGroup, [DataGroupTypes, DataInputTypes], {
  // more precise value type
  value: T.number,
  // custom props
  min: T.number,
  max: T.number,
  unit: T.string
})

export {
  NumberGroup
}
