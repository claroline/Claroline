import React, {Fragment, PureComponent} from 'react'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'

class BooleanInput extends PureComponent {
  constructor(props) {
    super(props)

    this.onChange = this.onChange.bind(this)
  }

  onChange(e) {
    this.props.onChange(e.target.checked)
  }

  render() {
    return (
      <Fragment>
        <input
          id={this.props.id}
          className="form-check-input"
          type="checkbox"
          checked={this.props.value}
          disabled={this.props.disabled}
          onChange={this.onChange}
          role="switch"
        />
        <label
          className="form-check-label"
          htmlFor={this.props.id}
        >
          {(this.props.value && this.props.labelChecked) ? this.props.labelChecked : this.props.label}
        </label>
      </Fragment>
    )
  }
}

implementPropTypes(BooleanInput, DataInputTypes, {
  value: T.bool,
  /**
   * @deprecated
   */
  labelChecked: T.string
}, {
  value: false
})

export {
  BooleanInput
}
