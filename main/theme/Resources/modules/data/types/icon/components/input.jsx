import React, {Component} from 'react'
import classes from 'classnames'

import {trans} from '#/main/app/intl/translation'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'

import {Button} from '#/main/app/action/components/button'
import {MENU_BUTTON} from '#/main/app/buttons'
import {IconCollection} from '#/main/theme/icon/containers/collection'

class IconInput extends Component {
  constructor(props) {
    super(props)

    this.onInputChange = this.onInputChange.bind(this)
  }

  onInputChange(e) {
    this.props.onChange(e.target.value)
  }

  renderPickerButton(className) {
    return (
      <Button
        className={classes('btn', className)}
        type={MENU_BUTTON}
        icon={`fa fa-fw fa-${this.props.value}`}
        label={trans('show-icons', {}, 'actions')}
        tooltip="right"
        size={this.props.size}
        disabled={this.props.disabled}
        menu={
          <div className="dropdown-menu">
            <IconCollection
              selected={this.props.value}
              onChange={this.props.onChange}
            />
          </div>
        }
      />
    )
  }

  render() {
    if (this.props.hideInput) {
      return this.renderPickerButton(this.props.className)
    }

    return (
      <div className={classes('input-group', this.props.className, {
        [`input-group-${this.props.size}`]: !!this.props.size
      })}>
        <span className="input-group-btn">
          {this.renderPickerButton()}
        </span>

        <input
          id={this.props.id}
          type="text"
          autoComplete={this.props.autoComplete}
          className="form-control"
          placeholder={this.props.placeholder}
          value={this.props.value || ''}
          disabled={this.props.disabled}
          onChange={this.onInputChange}
        />
      </div>
    )
  }
}

implementPropTypes(IconInput, FormFieldTypes, {
  // more precise value type
  value: T.string,

  // custom options
  hideInput: T.bool
}, {
  hideInput: false
})

export {
  IconInput
}
