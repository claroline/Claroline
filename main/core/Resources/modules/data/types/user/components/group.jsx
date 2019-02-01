import React from 'react'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'

import {FormGroupWithField as FormGroupWithFieldTypes} from '#/main/core/layout/form/prop-types'
import {FormGroup} from '#/main/app/content/form/components/group'
import {User as UserType} from '#/main/core/user/prop-types'
import {UserInput} from '#/main/core//data/types/user/components/input'

const UserGroup = props =>
  <FormGroup {...props}>
    <UserInput {...props} />
  </FormGroup>

implementPropTypes(UserGroup, FormGroupWithFieldTypes, {
  value: T.arrayOf(T.shape(UserType.propTypes))
})

export {
  UserGroup
}
