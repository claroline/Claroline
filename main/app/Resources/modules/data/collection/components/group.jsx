import React from 'react'
import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'

import {FormGroupWithField as FormGroupWithFieldTypes} from '#/main/core/layout/form/prop-types'
import {FormGroup} from '#/main/core/layout/form/components/group/form-group'

import {CollectionInput} from '#/main/app/data/collection/components/input'

const CollectionGroup = props =>
  <FormGroup {...props}>
    <CollectionInput {...props} />
  </FormGroup>

implementPropTypes(CollectionGroup, FormGroupWithFieldTypes, {
  // more precise value type
  value: T.array,
  // custom props
  min: T.number,
  max: T.number
}, {

})

export {
  CollectionGroup
}
