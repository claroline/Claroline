import React, {PureComponent} from 'react'
import classes from 'classnames'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'
import isEmpty from 'lodash/isEmpty'
import {getValidationClassName} from '#/main/app/content/form/validator'

const NumberField = props =>
  <input
    id={props.id}
    type="number"
    className={classes('form-control', props.className)}
    style={props.style}
    value={props.value}
    disabled={props.disabled}
    min={props.min}
    max={props.max}
    placeholder={props.placeholder}
    autoComplete={props.autoComplete}
    autoFocus={props.autoFocus}
    onChange={props.onChange}
  />

NumberField.propTypes = {
  id: T.string.isRequired,
  className: T.string,
  style: T.object,
  disabled: T.bool,
  placeholder: T.string,
  autoFocus: T.bool,
  autoComplete: T.string,
  value: T.oneOfType([T.number, T.string]),
  min: T.number,
  max: T.number,
  onChange: T.func.isRequired
}

class NumberInput extends PureComponent {
  constructor(props) {
    super(props)

    this.onChange = this.onChange.bind(this)
  }

  onChange(e) {
    if ('' !== e.target.value) {
      this.props.onChange(Number(e.target.value))
    } else {
      this.props.onChange(null)
    }
  }

  render() {
    const fieldProps = {
      id: this.props.id,
      disabled: this.props.disabled,
      placeholder: this.props.placeholder,
      autoFocus: this.props.autoFocus,
      autoComplete: this.props.autoComplete,
      style: this.props.style,
      value: null === this.props.value || isNaN(this.props.value) ? '' : this.props.value,
      min: this.props.min,
      max: this.props.max,
      onChange: this.onChange,
      'aria-required': this.props.required,
      'aria-invalid': !isEmpty(this.props.error)
    }

    if (this.props.unit) {
      return (
        <div className={classes('input-group', this.props.className, {
          [`input-group-${this.props.size}`]: !!this.props.size,
          'has-validation': !isEmpty(this.props.error)
        })} role="presentation">
          <NumberField
            {...fieldProps}
            className={getValidationClassName(this.props.error, this.props.validating)}
          />

          <span className="input-group-text">
            {this.props.unit}
          </span>
        </div>
      )
    }

    return (
      <NumberField
        {...fieldProps}
        className={classes(this.props.className, getValidationClassName(this.props.error, this.props.validating), {
          [`form-control-${this.props.size}`]: !!this.props.size
        })}
      />
    )
  }
}

implementPropTypes(NumberInput, DataInputTypes, {
  value: T.oneOfType([T.number, T.string]),
  min: T.number,
  max: T.number,
  unit: T.string
}, {
  value: ''
})

export {
  NumberInput
}
