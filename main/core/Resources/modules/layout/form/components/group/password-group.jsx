import React from 'react'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'

import {FormGroupWithField as FormGroupWithFieldTypes} from '#/main/core/layout/form/prop-types'
import {FormGroup} from '#/main/app/content/form/components/group.jsx'
import {Password} from '#/main/core/layout/form/components/field/password.jsx'

// todo : show current value complexity

const PasswordGroup = props =>
  <FormGroup {...props}>
    <Password {...props} />
  </FormGroup>

implementPropTypes(PasswordGroup, FormGroupWithFieldTypes, {
  // more precise value type
  value: T.string
})

export {
  PasswordGroup
}
