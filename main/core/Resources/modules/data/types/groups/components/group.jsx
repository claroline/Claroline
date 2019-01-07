import React from 'react'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'

import {FormGroupWithField as FormGroupWithFieldTypes} from '#/main/core/layout/form/prop-types'
import {FormGroup} from '#/main/app/content/form/components/group'
import {Group as GroupType} from '#/main/core/user/prop-types'
import {GroupsInput} from '#/main/core/data/types/groups/components/input'

const GroupsGroup = props =>
  <FormGroup {...props}>
    <GroupsInput {...props} />
  </FormGroup>

implementPropTypes(GroupsGroup, FormGroupWithFieldTypes, {
  value: T.arrayOf(T.shape(GroupType.propTypes))
})

export {
  GroupsGroup
}
