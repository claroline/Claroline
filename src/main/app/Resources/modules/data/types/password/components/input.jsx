import React, {PureComponent} from 'react'
import classes from 'classnames'

import {param} from '#/main/app/config'
import {trans} from '#/main/app/intl/translation'
import {match} from '#/main/app/data/types/validators'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {ProgressBar} from '#/main/app/content/components/progress-bar'

import {passwordStrength} from '#/main/app/data/types/password/utils'

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

  checkValidPassword(password) {
    let conditions = {}

    const minLength = param('authentication.password.minLength')
    if (minLength > 0) {
      conditions.minlength_rules = {
        text: minLength + ' ' + trans('minlength_rules', {}, 'security'),
        checked: password.length >= minLength
      }
    }

    if (param('authentication.password.requireLowercase')) {
      conditions.lowercase_rules = {
        text: trans('lowercase_rules', {}, 'security'),
        checked: !match(password, {regex: /[a-z]/})
      }
    }

    if (param('authentication.password.requireUppercase')) {
      conditions.uppercase_rules = {
        text: trans('uppercase_rules', {}, 'security'),
        checked: !match(password, {regex: /[A-Z]/})
      }
    }

    if (param('authentication.password.requireNumber')) {
      conditions.number_rules = {
        text: trans('number_rules', {}, 'security'),
        checked: !match(password, {regex: /[0-9]/})
      }
    }

    if (param('authentication.password.requireSpecialChar')) {
      conditions.special_rules = {
        text: trans('special_rules', {}, 'security'),
        checked: !match(password, {regex: /[^a-zA-Z0-9]/})
      }
    }

    this.setState({
      passwordValidChecks: Object.values(conditions)
    })
  }

  render() {
    const progressBarTypes = ['danger', 'warning', 'info', 'success']
    const progressBarType = this.state.passwordStrength > 0 ? progressBarTypes[this.state.passwordStrength - 1] : 'danger'
    const labels = [
      trans('password_strength.very_weak', {}, 'security'),
      trans('password_strength.weak', {}, 'security'),
      trans('password_strength.medium', {}, 'security'),
      trans('password_strength.strong', {}, 'security'),
      trans('password_strength.very_strong', {}, 'security')
    ]

    return (
      <>
        <div className={classes('input-group', this.props.className, {
          [`input-group-${this.props.size}`]: !!this.props.size
        })}>

          <span className="input-group-text">
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

          <Button
            className="btn btn-outline-secondary"
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

        {!this.props.hideStrength && this.props.value &&
          <>
            <ProgressBar
              className="password-strength"
              value={this.state.passwordStrength * 25}
              size="sm"
              type={progressBarType}
            />
            <div className="password-strength-label">
              <span className={`text-${progressBarType} strength-label`}>
                {labels[this.state.passwordStrength]}
              </span>
              <a className="label-link" href="https://www.ssi.gouv.fr/administration/precautions-elementaires/calculer-la-force-dun-mot-de-passe/" target="_blank" rel="noopener noreferrer">
                <span className="fa fa-fw fa-question-circle"/>
              </a>
            </div>
          </>
        }
        {!this.props.disablePasswordCheck &&
          <div className="password-rules">
            {this.state.passwordValidChecks.map((msg, index) =>
              <div className={'password-check' + (this.props.value.length > 0 ? ( msg.checked ? '-valid' : '-invalid') : '' )} key={index}>
                <span className={'fa fa-fw fa-' + (msg.checked ? 'check' : 'times' ) + '-circle icon-with-text-right'}/>
                <label className="validate-label">{msg.text}</label>
              </div>
            )}
          </div>
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
  value: ''
})

export {
  PasswordInput
}
