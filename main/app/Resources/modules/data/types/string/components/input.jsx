import React, {PureComponent} from 'react'
import classes from 'classnames'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'

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
      className: classes('form-control', this.props.className, {
        [`input-${this.props.size}`]: !!this.props.size
      }),
      value: this.props.value || '',
      disabled: this.props.disabled,
      onChange: this.onChange,
      placeholder: this.props.placeholder,
      autoComplete: this.props.autoComplete
    }

    if (this.props.long) {
      return (
        <textarea
          {...commonProps}
          rows={this.props.minRows}
        />
      )
    }

    return (
      <input
        {...commonProps}
        type="text"
      />
    )
  }
}

implementPropTypes(StringInput, DataInputTypes, {
  value: T.string,
  long: T.bool,
  minRows: T.number,
  minLength: T.number,
  maxLength: T.number
}, {
  value: '',
  long: false,
  minRows: 4
})

export {
  StringInput
}
