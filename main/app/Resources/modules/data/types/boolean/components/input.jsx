import React, {PureComponent} from 'react'
import classes from 'classnames'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'

// TODO : use main/app/input/components/checkbox

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
      <div className={classes({
        'checkbox'       : !this.props.inline,
        'checkbox-inline': this.props.inline
      })}>
        <label>
          <input
            id={this.props.id}
            type="checkbox"
            checked={this.props.value}
            disabled={this.props.disabled}
            onChange={this.onChange}
          />

          {(this.props.value && this.props.labelChecked) ? this.props.labelChecked : this.props.label}
        </label>
      </div>
    )
  }
}

implementPropTypes(BooleanInput, FormFieldTypes, {
  value: T.bool,
  labelChecked: T.string,
  inline: T.bool
}, {
  value: false,
  inline: false
})

export {
  BooleanInput
}
