import React, {Component} from 'react'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'

import {ResourceInput} from '#/main/core/data/types/resource/components/input'

// todo : fix responsive (incorrect margin bottom)
// todo : manages errors

class ResourceParticipatedInput extends Component {
  constructor(props) {
    super(props)
  }

  render() {
    return (
      <ResourceInput
        onChange={(value) => this.props.onChange(value)}
        value={this.props.value}
      />
    )
  }
}

implementPropTypes(ResourceParticipatedInput, FormFieldTypes, {
  // more precise value type
  value: T.arrayOf(T.string)


}, {
  value: null
})

export {
  ResourceParticipatedInput
}
