import React from 'react'
import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'

import {FormGroupWithField as FormGroupWithFieldTypes} from '#/main/core/layout/form/prop-types'
import {FormGroup} from '#/main/core/layout/form/components/group/form-group.jsx'
import {Email} from '#/main/core/layout/form/components/field/email.jsx'

const EmailGroup = props =>
  <FormGroup
    {...props}
  >
    <Email
      id={props.id}
      value={props.value}
      disabled={props.disabled}
      onChange={props.onChange}
    />
  </FormGroup>

implementPropTypes(EmailGroup, FormGroupWithFieldTypes, {
  // more precise value type
  value: T.string
}, {
  value: ''
})

export {
  EmailGroup
}
