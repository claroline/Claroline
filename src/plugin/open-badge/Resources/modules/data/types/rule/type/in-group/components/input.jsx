import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'
import {FormGroup} from '#/main/app/content/form/components/group'

import {GroupInput} from '#/main/community/data/types/group/components/input'
import {Group as GroupTypes} from '#/main/community/prop-types'

// todo : manages errors

const InGroupInput = (props) =>
  <FormGroup
    id={props.id}
    className="form-last"
    label={trans('group')}
  >
    <GroupInput {...props} />
  </FormGroup>

implementPropTypes(InGroupInput, DataInputTypes, {
  // more precise value type
  value: T.shape(
    GroupTypes.proptTypes
  )
}, {
  value: null
})

export {
  InGroupInput
}
