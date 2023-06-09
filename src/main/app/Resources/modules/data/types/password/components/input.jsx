import React, {PureComponent} from 'react'
import classes from 'classnames'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {trans} from '#/main/app/intl/translation'
import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'

import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

class PasswordInput extends PureComponent {
  constructor(props) {
    super(props)

    this.state = {
      visible: false,
      passwordStrength: 0,
      strengthMessage: []
    }

    this.onChange = this.onChange.bind(this)
    this.toggleVisibility = this.toggleVisibility.bind(this)
    this.estimatePasswordStrength = this.estimatePasswordStrength.bind(this)
  }

  onChange(e) {
    this.props.onChange(e.target.value)
    this.estimatePasswordStrength(e.target.value)
  }

  toggleVisibility() {
    this.setState({visible: !this.state.visible})
  }

  estimatePasswordStrength(password) {
    let strength = 0
    const conditions = [
      {regex: /[a-z]/, message: trans('password-strength-lowercase', {}, 'security')},
      {regex: /[A-Z]/, message: trans('password-strength-uppercase', {}, 'security')},
      {regex: /[0-9]/, message: trans('password-strength-number', {}, 'security')},
      {regex: /[^a-zA-Z0-9]/, message: trans('password-strength-special', {}, 'security')},
      {regex: /^.{8,}$/, message: trans('password-strength-length', {}, 'security')}
    ]

    conditions.forEach(condition => {
      if (condition.regex.test(password)) {
        strength++
      }
    })

    this.setState({
      passwordStrength: strength,
      strengthMessage: conditions.map((condition) => ({
        text: condition.message,
        checked: condition.regex.test(password)
      }))
    })
  }

  render() {
    return (
      <div>
        <div className={classes('input-group', this.props.className, {
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
            placeholder={this.props.placeholder}
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

        {!this.props.hideStrength &&
          <div className="password-rules">
            {this.state.strengthMessage.map((msg, index) =>
              <div className="password-rules-block" key={index}>
                <span className={'fa fa-2x fa-fw fa-' + (msg.checked ? 'check' : 'times' ) + '-circle' }/>
                <label className="password-rules-label">{msg.text}</label>
              </div>
            )}
          </div>
        }
      </div>

    )
  }
}

implementPropTypes(PasswordInput, DataInputTypes, {
  value: T.string
}, {
  value: ''
})

export {
  PasswordInput
}
