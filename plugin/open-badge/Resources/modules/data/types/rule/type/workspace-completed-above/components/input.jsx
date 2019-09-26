import React, {Component, Fragment} from 'react'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'

import {WorkspaceInput} from '#/main/core/data/types/workspace/components/input'
import {NumberInput} from '#/main/app/data/types/number/components/input'

// todo : fix responsive (incorrect margin bottom)
// todo : manages errors

class WorkspaceCompletedAboveInput extends Component {
  constructor(props) {
    super(props)
  }

  render() {
    return (
      <Fragment>
        <WorkspaceInput
          onChange={(value) => this.props.onChange({workspace: value})}
          value={this.props.value.workspace}
        />
        <NumberInput
          onChange = {(value) => this.props.onChange({value})}
          min={0}
          max={100}
          value={this.props.value.value}
        />
      </Fragment>
    )
  }
}

implementPropTypes(WorkspaceCompletedAboveInput, FormFieldTypes, {
  // more precise value type
  value: T.arrayOf(T.string)


}, {
  value: {workspace: null, value: null}
})

export {
  WorkspaceCompletedAboveInput
}
