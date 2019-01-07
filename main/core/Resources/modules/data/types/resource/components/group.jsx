import React from 'react'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'

import {FormGroupWithField as FormGroupWithFieldTypes} from '#/main/core/layout/form/prop-types'
import {FormGroup} from '#/main/app/content/form/components/group'

import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'
import {ResourceInput} from '#/main/core/data/types/resource/components/input'

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
