import React, {PureComponent} from 'react'
import classes from 'classnames'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {trans} from '#/main/app/intl/translation'
import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'

import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

class PasswordInput extends PureComponent {
  constructor(props) {
    super(props)

    this.state = {
      visible: false
    }

    this.onChange = this.onChange.bind(this)
    this.toggleVisibility = this.toggleVisibility.bind(this)
  }

  onChange(e) {
    this.props.onChange(e.target.value)
  }

  toggleVisibility() {
    this.setState({visible: !this.state.visible})
  }

  render() {
    return (
      <div className={classes('input-group', {
        [`input-group-${this.props.size}`]: !!this.props.size
      })}>
        <span className="input-group-addon">
          <span className="fa fa-fw fa-lock" role="presentation" />
        </span>

        <input
          id={this.props.id}
          type={this.state.visible ? 'text':'password'}
          className="form-control"
          value={this.props.value || ''}
          disabled={this.props.disabled}
          onChange={this.onChange}
          autoComplete={this.props.autoComplete}
        />

        <span className="input-group-btn">
          <Button
            className="btn"
            type={CALLBACK_BUTTON}
            icon={classes('fa fa-fw', {
              'fa-eye'      : !this.state.visible,
              'fa-eye-slash': this.state.visible
            })}
            label={trans(this.state.visible ? 'hide_password':'show_password')}
            disabled={this.props.disabled}
            callback={this.toggleVisibility}
            tooltip="left"
          />
        </span>
      </div>
    )
  }
}


implementPropTypes(PasswordInput, FormFieldTypes, {
  value: T.string
}, {
  value: ''
})

export {
  PasswordInput
}
