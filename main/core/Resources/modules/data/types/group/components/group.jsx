import React from 'react'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'

import {FormGroupWithField as FormGroupWithFieldTypes} from '#/main/core/layout/form/prop-types'
import {FormGroup} from '#/main/app/content/form/components/group'
import {Group as GroupType} from '#/main/core/user/prop-types'
import {GroupInput} from '#/main/core/data/types/group/components/input'

const GroupGroup = props =>
  <FormGroup {...props}>
    <GroupInput {...props} />
  </FormGroup>

implementPropTypes(GroupGroup, FormGroupWithFieldTypes, {
  value: T.arrayOf(T.shape(GroupType.propTypes))
})

export {
  GroupGroup
}
