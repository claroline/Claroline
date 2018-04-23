import React from 'react'
import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'

import {FormGroupWithField as FormGroupWithFieldTypes} from '#/main/core/layout/form/prop-types'
import {FormGroup} from '#/main/core/layout/form/components/group/form-group'
import {Storage} from '#/main/core/layout/form/components/field/storage'

const StorageGroup = props =>
  <FormGroup {...props}>
    <Storage {...props} />
  </FormGroup>

implementPropTypes(StorageGroup, FormGroupWithFieldTypes, {
  // more precise value type
  value: T.number
}, {

})

export {
  StorageGroup
}
