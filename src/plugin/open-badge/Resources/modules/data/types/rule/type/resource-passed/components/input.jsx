import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'
import {FormGroup} from '#/main/app/content/form/components/group'

import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'
import {ResourceInput} from '#/main/core/data/types/resource/components/input'

// todo : manages errors

const ResourcePassedInput = (props) =>
  <FormGroup
    id={props.id}
    className="form-last"
    label={trans('resource')}
  >
    <ResourceInput {...props} />
  </FormGroup>

implementPropTypes(ResourcePassedInput, DataInputTypes, {
  // more precise value type
  value: T.shape(
    ResourceNodeTypes.propTypes
  )
}, {
  value: null
})

export {
  ResourcePassedInput
}
