import React from 'react'
import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'

import {FormGroupWithField as FormGroupWithFieldTypes} from '#/main/core/layout/form/prop-types'
import {FormGroup} from '#/main/core/layout/form/components/group/form-group.jsx'
import {Text} from '#/main/core/layout/form/components/field/text.jsx'

// todo check uniqueness (maybe do it in the data type)
// todo add username requirements

const UsernameGroup = props =>
  <FormGroup {...props}>
    <div className="input-group">
      <span className="input-group-addon">
        <span className="fa fa-fw fa-user" role="presentation" />
      </span>

      <Text
        id={props.id}
        value={props.value}
        disabled={props.disabled}
        onChange={props.onChange}
      />
    </div>
  </FormGroup>

implementPropTypes(UsernameGroup, FormGroupWithFieldTypes, {
  // more precise value type
  value: T.string
}, {
  value: ''
})

export {
  UsernameGroup
}