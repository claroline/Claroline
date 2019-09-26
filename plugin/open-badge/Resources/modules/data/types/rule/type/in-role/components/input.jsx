import React, {Component} from 'react'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'

import {RoleInput} from '#/main/core/data/types/role/components/input'

// todo : fix responsive (incorrect margin bottom)
// todo : manages errors

class InRoleInput extends Component {
  constructor(props) {
    super(props)
  }

  render() {
    return (
      <RoleInput
        onChange={(value) => this.props.onChange(value)}
        value={this.props.value}
      />
    )
  }
}

implementPropTypes(InRoleInput, FormFieldTypes, {
  // more precise value type
  value: T.arrayOf(T.string)


}, {
  value: null
})

export {
  InRoleInput
}
