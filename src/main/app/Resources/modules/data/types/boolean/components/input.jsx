import React, {PureComponent} from 'react'
import classes from 'classnames'

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
      <div className="form-check form-switch form-check-reverse fw-medium fs-4 d-flex flex-row flex-nowrap align-items-center mb-0" role="presentation">
        <label
          className={classes('form-check-label flex-fill fs-base text-start', {
            'text-body-secondary': !this.props.value
          })}
          htmlFor={this.props.id}
        >
          {this.props.label}
        </label>

        <input
          id={this.props.id}
          className="form-check-input"
          type="checkbox"
          checked={this.props.value}
          disabled={this.props.disabled}
          onChange={this.onChange}
          aria-checked={this.props.value}
          role="switch"
        />
      </div>
    )
  }
}

implementPropTypes(BooleanInput, DataInputTypes, {
  value: T.bool
}, {
  value: false
})

export {
  BooleanInput
}
