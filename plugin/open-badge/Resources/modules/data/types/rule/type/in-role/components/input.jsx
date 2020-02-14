import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'
import {FormGroup} from '#/main/app/content/form/components/group'

import {RoleInput} from '#/main/core/data/types/role/components/input'
import {Role as RoleTypes} from '#/main/core/user/prop-types'

// todo : manages errors

const InRoleInput = (props) =>
  <FormGroup
    id={props.id}
    className="form-last"
    label={trans('role')}
  >
    <RoleInput {...props} />
  </FormGroup>

implementPropTypes(InRoleInput, FormFieldTypes, {
  // more precise value type
  value: T.shape(
    RoleTypes.propTypes
  )
}, {
  value: null
})

export {
  InRoleInput
}
