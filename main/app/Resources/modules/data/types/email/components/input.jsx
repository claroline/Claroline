import React, {PureComponent} from 'react'
import classes from 'classnames'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'

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
        className={classes('form-control', this.props.className, {
          [`input-${this.props.size}`]: !!this.props.size
        })}
        autoComplete={this.props.autoComplete || 'email'}
        value={this.props.value || ''}
        disabled={this.props.disabled}
        placeholder={this.props.placeholder}
        onChange={this.onChange}
      />
    )
  }
}

implementPropTypes(EmailInput, DataInputTypes, {
  // more precise value type
  value: T.string
}, {
  value: ''
})

export {
  EmailInput
}
