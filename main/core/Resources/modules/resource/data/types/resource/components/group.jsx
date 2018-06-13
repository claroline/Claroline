import React from 'react'
import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'

import {FormGroupWithField as FormGroupWithFieldTypes} from '#/main/core/layout/form/prop-types'
import {FormGroup} from '#/main/core/layout/form/components/group/form-group'

import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/data/types/resource/prop-types'
import {ResourceInput} from '#/main/core/resource/data/types/resource/components/input'

const ResourceGroup = props =>
  <FormGroup {...props}>
    <ResourceInput {...props} />
  </FormGroup>

implementPropTypes(ResourceGroup, FormGroupWithFieldTypes, {
  // more precise value type
  value: T.shape(
    ResourceNodeTypes.propTypes
  )
})

export {
  ResourceGroup
}
