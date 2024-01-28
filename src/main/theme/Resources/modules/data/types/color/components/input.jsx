import React, {Component, forwardRef} from 'react'
import classes from 'classnames'
import tinycolor from 'tinycolor2'

import {trans} from '#/main/app/intl/translation'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'

import {Button} from '#/main/app/action/components/button'
import {COLOR_BUTTON} from '#/main/theme/buttons'


class ColorInput extends Component {
  constructor(props) {
    super(props)

    this.onInputChange = this.onInputChange.bind(this)
    this.onInputBlur = this.onInputBlur.bind(this)
  }

  onInputChange(e) {
    this.props.onChange(e.target.value)
  }

  onInputBlur(e) {
    // format color string
    const color = tinycolor(e.target.value)
    if (color.isValid()) {
      if (1 > color.getAlpha()) {
        // convert to rgba
        this.props.onChange(color.toRgbString())
      } else {
        // no alpha => convert to hex
        this.props.onChange(color.toHexString())
      }
    }
  }

  renderPickerButton(className) {
    return (
      <Button
        id={`color-picker-${this.props.id}`}
        className={classes(className, !this.props.hideInput && 'rounded-end-0')}
        type={COLOR_BUTTON}
        icon={this.props.colorIcon}
        label={trans('show-colors', {}, 'actions')}
        tooltip="right"
        size={this.props.size}
        disabled={this.props.disabled}
        color={this.props.value}
        onSelect={this.props.onChange}
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
        {this.renderPickerButton('rounded-end-0')}

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

implementPropTypes(ColorInput, DataInputTypes, {
  // more precise value type
  value: T.string,

  // custom options
  hideInput: T.bool,
  colorIcon: T.string
}, {
  hideInput: false,
  colorIcon: 'fa fa-fw fa-palette'
})

export {
  ColorInput
}
