import React, {PureComponent} from 'react'
import classes from 'classnames'

import {trans} from '#/main/app/intl/translation'

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
      passwordStrength: 0
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
    this.setState({
      passwordStrength: passwordStrength(password)
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
      </>
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
