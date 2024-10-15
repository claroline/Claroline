import React, {PureComponent} from 'react'
import classes from 'classnames'
import isEmpty from 'lodash/isEmpty'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'
import {getValidationClassName} from '#/main/app/content/form/validator'

class PhoneInput extends PureComponent {
  constructor(props) {
    super(props)

    this.onChange = this.onChange.bind(this)
  }

  onChange(e) {
    this.props.onChange(e.target.value.trim())
  }

  render() {
    return (
      <input
        id={this.props.id}
        type="phone"
        className={classes('form-control', getValidationClassName(this.props.error, this.props.validating), this.props.className, {
          [`form-control-${this.props.size}`]: !!this.props.size
        })}
        autoComplete={this.props.autoComplete || 'tel-local'}
        value={this.props.value || ''}
        disabled={this.props.disabled}
        placeholder={this.props.placeholder}
        onChange={this.onChange}
        aria-required={this.props.required}
        aria-invalid={!isEmpty(this.props.error)}
      />
    )
  }
}

implementPropTypes(PhoneInput, DataInputTypes, {
  // more precise value type
  value: T.string
}, {
  value: ''
})

export {
  PhoneInput
}
