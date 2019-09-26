import React, {Component} from 'react'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'

import {GroupInput} from '#/main/core/data/types/group/components/input'

// todo : fix responsive (incorrect margin bottom)
// todo : manages errors

class InGroupInput extends Component {
  constructor(props) {
    super(props)
  }

  render() {
    return (
      <GroupInput
        onChange={(value) => this.props.onChange(value)}
        value={this.props.value}
      />
    )
  }
}

implementPropTypes(InGroupInput, FormFieldTypes, {
  // more precise value type
  value: T.arrayOf(T.string)


}, {
  value: null
})

export {
  InGroupInput
}
