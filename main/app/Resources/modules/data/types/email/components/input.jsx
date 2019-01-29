import React, {PureComponent} from 'react'
import classes from 'classnames'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'

class EmailInput extends PureComponent {
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
        type="email"
        className={classes('form-control', {
          [`input-${this.props.size}`]: !!this.props.size
        })}
        autoComplete={this.props.autoComplete || 'email'}
        value={this.props.value || ''}
        disabled={this.props.disabled}
        onChange={this.onChange}
      />
    )
  }
}

implementPropTypes(EmailInput, FormFieldTypes, {
  // more precise value type
  value: T.string
}, {
  value: ''
})

export {
  EmailInput
}
