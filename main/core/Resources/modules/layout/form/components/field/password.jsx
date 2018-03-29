import React, {Component} from 'react'

import {t} from '#/main/core/translation'

import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'
import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'

import {TooltipElement} from '#/main/core/layout/components/tooltip-element.jsx'

class Password extends Component {
  constructor(props) {
    super(props)

    this.state = {
      visible: false
    }
  }

  setPasswordVisibility(visibility) {
    this.setState({
      visible: visibility
    })
  }

  render() {
    return (
      <div className="input-group">
        <span className="input-group-addon">
          <span className="fa fa-fw fa-lock" role="presentation" />
        </span>

        <input
          id={this.props.id}
          type={this.state.visible ? 'text':'password'}
          className="form-control"
          autoComplete="new-password"
          value={this.props.value || ''}
          disabled={this.props.disabled}
          onChange={(e) => this.props.onChange(e.target.value)}
        />

        <span className="input-group-btn">
          <TooltipElement
            id={`${this.props.id}-show`}
            tip={t('show_password')}
          >
            <button
              type="button"
              role="button"
              className="btn btn-default"
              disabled={this.props.disabled}
              onMouseDown={() => this.setPasswordVisibility(true)}
              onMouseUp={() => this.setPasswordVisibility(false)}
            >
              <span className="fa fa-fw fa-eye" />
            </button>
          </TooltipElement>
        </span>
      </div>
    )
  }
}


implementPropTypes(Password, FormFieldTypes, {
  value: T.string
})

export {
  Password
}
