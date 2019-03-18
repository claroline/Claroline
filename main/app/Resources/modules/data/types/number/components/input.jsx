import React, {PureComponent} from 'react'
import classes from 'classnames'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'

const NumberField = props =>
  <input
    id={props.id}
    type="number"
    className={classes('form-control', props.className)}
    value={props.value}
    disabled={props.disabled}
    min={props.min}
    max={props.max}
    placeholder={props.placeholder}
    autoComplete={props.autoComplete}
    onChange={props.onChange}
  />

NumberField.propTypes = {
  id: T.string.isRequired,
  className: T.string,
  disabled: T.bool,
  placeholder: T.string,
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
      autoComplete: this.props.autoComplete,
      value: null === this.props.value || isNaN(this.props.value) ? '' : this.props.value,
      min: this.props.min,
      max: this.props.max,
      onChange: this.onChange
    }

    if (this.props.unit) {
      return (
        <div className={classes('input-group', this.props.className, {
          [`input-group-${this.props.size}`]: !!this.props.size
        })}>
          <NumberField
            {...fieldProps}
          />

          <span className="input-group-addon">
            {this.props.unit}
          </span>
        </div>
      )
    }

    return (
      <NumberField
        {...fieldProps}
        className={classes(this.props.className, {
          [`input-${this.props.size}`]: !!this.props.size
        })}
      />
    )
  }
}

implementPropTypes(NumberInput, FormFieldTypes, {
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
