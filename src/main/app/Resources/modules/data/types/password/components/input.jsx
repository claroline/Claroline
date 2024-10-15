import React, {PureComponent} from 'react'
import classes from 'classnames'

import {param} from '#/main/app/config'
import {trans} from '#/main/app/intl/translation'
import {match} from '#/main/app/data/types/validators'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

import {passwordStrength} from '#/main/app/data/types/password/utils'
import {getValidationClassName} from '#/main/app/content/form/validator'
import isEmpty from 'lodash/isEmpty'

class PasswordInput extends PureComponent {
  constructor(props) {
    super(props)

    this.state = {
      visible: false,
      passwordStrength: 0,
      passwordValidChecks: []
    }

    this.onChange = this.onChange.bind(this)
    this.toggleVisibility = this.toggleVisibility.bind(this)
    this.estimatePasswordStrength = this.estimatePasswordStrength.bind(this)
    this.checkValidPassword = this.checkValidPassword.bind(this)
  }

  componentDidMount() {
    this.checkValidPassword(this.props.value)
  }

  onChange(e) {
    this.props.onChange(e.target.value)
    this.estimatePasswordStrength(e.target.value)
    this.checkValidPassword(e.target.value)
  }

  toggleVisibility() {
    this.setState({visible: !this.state.visible})
  }

  estimatePasswordStrength(password) {
    this.setState({
      passwordStrength: passwordStrength(password)
    })
  }

  checkValidPassword(password = '') {
    const conditions = []

    if (this.props.disablePasswordCheck) {
      return
    }

    const minLength = param('authentication.password.minLength')
    if (minLength > 0) {
      conditions.push({
        text: minLength + ' ' + trans('minlength_rules', {}, 'security'),
        checked: password.length >= minLength
      })
    }

    if (param('authentication.password.requireLowercase')) {
      conditions.push({
        text: trans('lowercase_rules', {}, 'security'),
        checked: !match(password, {regex: /[a-z]/})
      })
    }

    if (param('authentication.password.requireUppercase')) {
      conditions.push({
        text: trans('uppercase_rules', {}, 'security'),
        checked: !match(password, {regex: /[A-Z]/})
      })
    }

    if (param('authentication.password.requireNumber')) {
      conditions.push({
        text: trans('number_rules', {}, 'security'),
        checked: !match(password, {regex: /[0-9]/})
      })
    }

    if (param('authentication.password.requireSpecialChar')) {
      conditions.push({
        text: trans('special_rules', {}, 'security'),
        checked: !match(password, {regex: /[^a-zA-Z0-9]/})
      })
    }

    this.setState({
      passwordValidChecks: conditions
    })
  }

  render() {
    const labels = [
      trans('password_strength.very_weak', {}, 'security'),
      trans('password_strength.weak', {}, 'security'),
      trans('password_strength.medium', {}, 'security'),
      trans('password_strength.strong', {}, 'security')
    ]

    return (
      <>
        <div className={classes('input-group', this.props.className, {
          [`input-group-${this.props.size}`]: !!this.props.size,
          'has-validation': !isEmpty(this.props.error)
        })} role="presentation">
          <input
            id={this.props.id}
            type={this.state.visible ? 'text':'password'}
            className={classes('form-control', getValidationClassName(this.props.error, this.props.validating))}
            value={this.props.value || ''}
            disabled={this.props.disabled}
            onChange={this.onChange}
            placeholder={this.props.placeholder}
            autoComplete={this.props.autoComplete}
          />

          <Button
            className="btn btn-body"
            type={CALLBACK_BUTTON}
            icon={classes('fa fa-fw', {
              'fa-eye'      : !this.state.visible,
              'fa-eye-slash': this.state.visible
            })}
            label={trans(this.state.visible ? 'hide_password':'show_password')}
            disabled={this.props.disabled}
            callback={this.toggleVisibility}
            tooltip="left"
            size={this.props.size}
          />
        </div>

        {!this.props.hideStrength &&
          <>
            <div className="d-flex flex-row align-items-stretch gap-2 mt-2" role="presentation">
              {labels.map((label, index) =>
                <div key={label} className={classes('p-1 flex-fill rounded-1', {
                  'bg-body-secondary': !this.props.value || index > this.state.passwordStrength,
                  'bg-danger': 0 === this.state.passwordStrength,
                  'bg-warning': 1 === this.state.passwordStrength,
                  'bg-success': 2 <= this.state.passwordStrength
                })} />
              )}
            </div>
            <span className="text-body-secondary d-block text-end fs-sm mt-1">{labels[this.state.passwordStrength]}</span>
          </>
        }

        {!this.props.disablePasswordCheck && this.state.passwordValidChecks &&
          <ul className="list-unstyled mb-0">
            {this.state.passwordValidChecks.map((msg, index) =>
              <li key={index} className={classes('form-text', {
                'text-success': msg.checked
              })}>
                <span className={classes('fa fa-fw me-2', {
                  'fa-check-circle': msg.checked,
                  'far fa-times-circle': !msg.checked
                })} aria-hidden={true} />
                {msg.text}
              </li>
            )}
          </ul>
        }
      </>
    )
  }
}

implementPropTypes(PasswordInput, DataInputTypes, {
  value: T.string,
  hideStrength: T.bool,
  disablePasswordCheck: T.bool
}, {
  value: '',
  autoComplete: 'new-password'
})

export {
  PasswordInput
}
