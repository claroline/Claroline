import React, {PureComponent} from 'react'
import classes from 'classnames'
import isEmpty from 'lodash/isEmpty'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'
import {getValidationClassName} from '#/main/app/content/form/validator'
import {Textarea} from '#/main/app/input/components/textarea'

class StringInput extends PureComponent {
  constructor(props) {
    super(props)

    this.onChange = this.onChange.bind(this)
  }

  onChange(e) {
    this.props.onChange(e.target.value)
  }

  render() {
    const commonProps = {
      id: this.props.id,
      value: this.props.value || '',
      disabled: this.props.disabled,
      placeholder: this.props.placeholder,
      autoComplete: this.props.autoComplete,
      autoFocus: this.props.autoFocus,
      'aria-required': this.props.required,
      'aria-invalid': !isEmpty(this.props.error)
    }

    if (this.props.long) {
      return (
        <Textarea
          {...commonProps}
          className={classes(this.props.className, getValidationClassName(this.props.error))}
          size={this.props.size}
          minRows={this.props.minRows}
          autoResize={this.props.autoResize}
          onChange={this.props.onChange}
        />
      )
    }

    return (
      <input
        {...commonProps}
        className={classes('form-control', this.props.className, getValidationClassName(this.props.error), {
          [`form-control-${this.props.size}`]: !!this.props.size
        })}
        type="text"
        onChange={this.onChange}
      />
    )
  }
}

implementPropTypes(StringInput, DataInputTypes, {
  value: T.string,
  long: T.bool,
  minRows: T.number,
  autoResize: T.bool,
  minLength: T.number,
  maxLength: T.number
}, {
  value: '',
  long: false,
  minRows: 4,
  autoResize: true
})

export {
  StringInput
}
