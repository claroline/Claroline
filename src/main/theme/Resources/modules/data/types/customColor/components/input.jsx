import React, {Component} from 'react'
import classes from 'classnames'

import {trans} from '#/main/app/intl/translation'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'

import {Button} from '#/main/app/action/components/button'
import {MENU_BUTTON} from '#/main/app/buttons'
import {ColorChart} from '#/main/theme/color/components/color-chart'

class CustomColorInput extends Component {
  constructor(props) {
    super(props)

    this.onInputChange = this.onInputChange.bind(this)
  }

  onInputChange(e) {
    this.props.onChange(e.target.value)
  }

  renderCustomPickerButton(className) {
    return (
      <Button
        className={classes('btn', className)}
        style={{background: this.props.value, borderColor: this.props.value}}
        type={MENU_BUTTON}
        icon={`fa fa-fw fa-${this.props.value}`}
        label={trans('show-colors', {}, 'actions')}
        tooltip="right"
        size={this.props.size}
        disabled={this.props.disabled}
        menu={
          <div className="dropdown-menu">
            <ColorChart
              id={this.props.id}
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
      return this.renderCustomPickerButton(this.props.className)
    }

    return (
      <div className={classes('input-group', this.props.className, {
        [`input-group-${this.props.size}`]: !!this.props.size
      })}>
        <span className="input-group-btn">
          {this.renderCustomPickerButton()}
        </span>

        <input
          id={this.props.id}
          type="text"
          autoComplete={this.props.autoComplete}
          className="form-control"
          placeholder={this.props.placeholder || '#FFFFFF'}
          value={this.props.value || ''}
          disabled={this.props.disabled}
          onChange={this.onInputChange}
          onBlur={this.onInputBlur}
        />
      </div>
    )
  }
}

implementPropTypes(CustomColorInput, DataInputTypes, {
  // more precise value type
  value: T.string,

  // custom options
  hideInput: T.bool,
  colorIcon: T.string,
  colors: T.arrayOf(T.string)
}, {
  hideInput: true,
  colors: []
})

export {
  CustomColorInput
}
