import React, {Component} from 'react'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'

import {WorkspaceInput} from '#/main/core/data/types/workspace/components/input'

// todo : fix responsive (incorrect margin bottom)
// todo : manages errors

class WorkspacePassedInput extends Component {
  constructor(props) {
    super(props)
  }

  render() {
    return (
      <WorkspaceInput
        onChange={(value) => this.props.onChange(value)}
        value={this.props.value}
      />
    )
  }
}

implementPropTypes(WorkspacePassedInput, FormFieldTypes, {
  // more precise value type
  value: T.arrayOf(T.string)
}, {
  value: null
})

export {
  WorkspacePassedInput
}
